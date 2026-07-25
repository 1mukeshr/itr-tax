<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Document;
use App\Models\DocumentRequest;
use App\Models\ItrFiling;
use App\Models\Note;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Receipt;
use App\Models\StatusLog;
use App\Models\User;
use App\Support\AisReconcile;
use App\Support\ExpertAssigner;
use App\Support\Form16Helper;
use App\Support\PaymentGateway;
use App\Support\Validation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function dashboard()
    {
        if (! profileIsComplete(Auth::user())) {
            return redirect()->route('user.complete-profile')
                ->with('info', 'Complete your profile to start filing.');
        }

        $uid = Auth::id();
        $filings = ItrFiling::with(['plan', 'ca'])
            ->where('user_id', $uid)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $stats = [
            'total' => ItrFiling::where('user_id', $uid)->count(),
            'active' => ItrFiling::where('user_id', $uid)->whereNotIn('status', ['completed', 'cancelled', 'filed'])->count(),
            'filed' => ItrFiling::where('user_id', $uid)->whereIn('status', ['filed', 'completed'])->count(),
        ];

        $continueFiling = $filings->first(fn ($f) => ! in_array($f->status, ['completed', 'cancelled', 'filed'], true));

        return view('user.dashboard', compact('filings', 'stats', 'continueFiling'));
    }

    public function completeProfile()
    {
        return view('user.complete-profile', ['user' => Auth::user(), 'onboarding' => true]);
    }

    public function saveCompleteProfile(Request $request)
    {
        $user = Auth::user();
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => Validation::phone(),
            'pan' => Validation::pan(),
            'address' => 'nullable|string|max:500',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'pincode' => Validation::pincodeOptional(),
        ]);

        $user->update([
            'name' => $data['name'],
            'phone' => $data['phone'],
        ]);

        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'pan' => strtoupper($data['pan']),
                'address' => $data['address'] ?? null,
                'city' => $data['city'],
                'state' => $data['state'],
                'pincode' => $data['pincode'] ?? null,
            ]
        );

        $user->load('profile');

        $mode = session()->pull('pending_filing_mode');
        $planId = session()->pull('pending_plan_id');

        if (in_array($mode, ['self', 'assisted'], true)) {
            return redirect()->route('user.choose-service', array_filter([
                'mode' => $mode,
                'plan_id' => $planId ?: null,
            ]))->with('success', 'Profile saved. Choose how you want to file.');
        }

        return redirect()->route('user.dashboard')
            ->with('success', 'Profile saved. You can start filing anytime.');
    }

    public function chooseService(Request $request)
    {
        if (! profileIsComplete(Auth::user())) {
            return redirect()->route('user.complete-profile');
        }

        $plans = Plan::where('is_active', true)->where('slug', '!=', 'self-free')->orderBy('sort_order')->get();
        // Neutral chooser unless mode is explicitly set (self | assisted).
        $mode = match ($request->query('mode')) {
            'self' => 'self',
            'assisted' => 'assisted',
            default => null,
        };
        $planId = (int) $request->query('plan_id');
        $profile = $request->query('profile', 'salaried');

        return view('user.choose-service', compact('plans', 'mode', 'planId', 'profile'));
    }

    /** @deprecated alias */
    public function startFiling(Request $request)
    {
        return $this->chooseService($request);
    }

    public function createFiling(Request $request)
    {
        if (! profileIsComplete(Auth::user())) {
            return redirect()->route('user.complete-profile');
        }

        $request->validate([
            'filing_mode' => 'required|in:self,assisted',
            'income_profile' => 'nullable|string|max:50',
            'itr_type' => 'required|in:ITR-1,ITR-2,ITR-3,ITR-4',
            'pan' => Validation::panOptional(),
            'plan_id' => 'nullable|integer|exists:plans,id',
        ]);

        $mode = $request->input('filing_mode') === 'self' ? 'self' : 'assisted';
        $profile = $request->input('income_profile', 'salaried');
        $itrType = $request->input('itr_type');

        if ($mode === 'self') {
            $plan = Plan::where('slug', 'self-free')->where('is_active', true)->first();
        } else {
            $plan = Plan::where('id', $request->input('plan_id'))->where('is_active', true)->first();
            if (! $plan) {
                return redirect()->route('user.choose-service', ['mode' => 'assisted'])->with('error', 'Please select an expert plan.');
            }
        }

        $filing = ItrFiling::create([
            'user_id' => Auth::id(),
            'plan_id' => $plan?->id,
            'assessment_year' => config('itr.assessment_year'),
            'itr_type' => $itrType,
            'filing_mode' => $mode,
            'income_profile' => $profile,
            'status' => 'questionnaire_pending',
            'pan' => strtoupper($request->input('pan', '')) ?: Auth::user()->pan,
            'amount' => (float) ($plan?->price ?? 0),
        ]);

        logFilingStatus($filing->id, null, 'questionnaire_pending', Auth::id(), 'Service chosen — answer a few questions next');

        return redirect()->route('user.questions', $filing)->with('success', 'Service selected. Answer 10 quick questions.');
    }

    public function questions(ItrFiling $filing)
    {
        $this->authorizeFiling($filing);
        $questions = questionnaireQuestions();
        $prefill = $this->decodePrefill($filing);
        $answers = $prefill['questionnaire'] ?? [];
        $step = 'questions';

        return view('user.questions', compact('filing', 'questions', 'answers', 'step'));
    }

    public function saveQuestions(Request $request, ItrFiling $filing)
    {
        $this->authorizeFiling($filing);

        if (in_array($filing->status, ['filed', 'completed', 'cancelled'], true)) {
            return redirect()->to(filingContinueUrl($filing))->with('info', 'This filing is already closed.');
        }

        $questions = questionnaireQuestions();
        $rules = [];
        foreach ($questions as $key => $meta) {
            $options = array_keys($meta['options'] ?? []);
            $rules['q_'.$key] = ['required', 'string', Rule::in($options)];
        }
        $data = $request->validate($rules);
        $keys = array_keys($questions);

        $answers = [];
        foreach ($keys as $key) {
            $answers[$key] = $data['q_'.$key];
        }

        $employment = $answers['employment'] ?? 'salaried';
        $profileMap = [
            'salaried' => 'salaried',
            'business' => 'freelancer',
            'freelancer' => 'freelancer',
            'investor' => 'investor',
            'other' => 'salaried',
        ];
        $incomeProfile = $profileMap[$employment] ?? 'salaried';
        if (($answers['foreign_income'] ?? '') === 'yes') {
            $incomeProfile = 'nri';
        }
        if (($answers['crypto_fno'] ?? '') === 'yes') {
            $incomeProfile = 'advanced_trader';
        }

        $itrType = suggestItrType($incomeProfile);
        if (($answers['capital_gains'] ?? '') === 'yes' || ($answers['house_property'] ?? '') === 'yes') {
            $itrType = $itrType === 'ITR-1' ? 'ITR-2' : $itrType;
        }

        $regime = match ($answers['tax_regime'] ?? 'unsure') {
            'old' => 'old',
            'new' => 'new',
            default => 'new',
        };

        $prefill = $this->decodePrefill($filing);
        $advance = in_array($filing->status, ['questionnaire_pending', 'draft'], true);
        $payload = [
            'income_profile' => $incomeProfile,
            'itr_type' => $filing->itr_type ?: $itrType,
            'tax_regime' => $regime,
            'notes' => json_encode(array_merge($prefill, [
                'questionnaire' => $answers,
                'suggested_itr' => $itrType,
            ])),
        ];

        if ($advance) {
            $oldStatus = $filing->status;
            $payload['status'] = 'documents_pending';
            $filing->update($payload);
            logFilingStatus($filing->id, $oldStatus, 'documents_pending', Auth::id(), 'Questionnaire saved — upload documents');

            return redirect()->route('user.documents', $filing)->with('success', 'Answers saved. Upload your documents next.');
        }

        $filing->update($payload);

        return redirect()->to(filingContinueUrl($filing))->with('success', 'Answers updated.');
    }

    public function documents(ItrFiling $filing)
    {
        $this->authorizeFiling($filing);
        $docs = Document::where('filing_id', $filing->id)->orderByDesc('created_at')->get();
        $requests = DocumentRequest::where('filing_id', $filing->id)->orderByDesc('created_at')->get();
        $docTypes = [
            'form16' => 'Form 16',
            'form26as' => 'Form 26AS / AIS',
            'pan_card' => 'PAN Card',
            'aadhaar' => 'Aadhaar Card',
            'bank_statement' => 'Bank Interest Certificate',
            'investment_proof' => 'Investment Proofs (80C/80D)',
            'capital_gains' => 'Capital Gains Statement',
            'other' => 'Other',
        ];
        $step = 'docs';
        $uploadedTypes = $docs->pluck('doc_type')->unique()->values()->all();
        $mismatchHints = Form16Helper::mismatchHints($uploadedTypes);
        $prefill = $this->decodePrefill($filing);

        return view('user.documents', compact('filing', 'docs', 'requests', 'docTypes', 'step', 'mismatchHints', 'prefill'));
    }

    public function uploadDocument(Request $request, ItrFiling $filing)
    {
        $this->authorizeFiling($filing);

        $data = $request->validate([
            'document' => 'required|file|max:10240|mimes:pdf,jpg,jpeg,png,zip',
            'doc_type' => ['required', 'string', Rule::in(Validation::docTypes())],
        ]);

        $uploaded = uploadItrFile($request->file('document'), 'uploads');
        if (! $uploaded) {
            return back()->with('error', 'Upload failed. Allowed: PDF, JPG, PNG, ZIP (max 10MB).');
        }

        $docType = $data['doc_type'];

        Document::create([
            'filing_id' => $filing->id,
            'user_id' => Auth::id(),
            'doc_type' => $docType,
            'original_name' => $uploaded['original_name'],
            'file_path' => $uploaded['file_path'],
            'file_size' => $uploaded['file_size'],
            'mime_type' => $uploaded['mime_type'],
            'uploaded_by' => Auth::id(),
        ]);

        DocumentRequest::where('filing_id', $filing->id)->where('status', 'open')->update(['status' => 'fulfilled']);

        if ($filing->status === 'docs_requested') {
            $oldStatus = $filing->status;
            $filing->update(['status' => 'under_review']);
            logFilingStatus($filing->id, $oldStatus, 'under_review', Auth::id(), 'Client re-uploaded documents — back to expert review');
            if ($filing->ca_id) {
            }

            return back()->with('success', 'Documents uploaded. Your tax expert will continue the review.');
        }

        return back()->with('success', 'Document uploaded successfully.');
    }

    public function continueAfterDocuments(ItrFiling $filing)
    {
        $this->authorizeFiling($filing);

        $hasForm16 = Document::where('filing_id', $filing->id)->where('doc_type', 'form16')->exists();
        $docCount = Document::where('filing_id', $filing->id)->count();
        if ($docCount < 1) {
            return back()->with('error', 'Upload at least one document before continuing.');
        }
        if (! $hasForm16) {
            return back()->with('error', 'Form 16 is required before you can continue.');
        }

        if ($filing->filing_mode === 'self') {
            if (in_array($filing->status, ['summary_pending', 'ready_to_file', 'filed', 'completed'], true)) {
                return redirect()->to(filingContinueUrl($filing));
            }
            if (! in_array($filing->status, ['documents_pending', 'docs_requested', 'draft'], true)) {
                return redirect()->to(filingContinueUrl($filing));
            }
            $oldStatus = $filing->status;
            $filing->update(['status' => 'summary_pending']);
            logFilingStatus($filing->id, $oldStatus, 'summary_pending', Auth::id(), 'Documents uploaded — enter tax summary');

            return redirect()->route('user.summary', $filing)->with('success', 'Documents received. Enter your income figures from Form 16.');
        }

        if ($filing->status === 'details_review') {
            return redirect()->route('user.review-details', $filing);
        }

        if (! in_array($filing->status, ['documents_pending', 'draft'], true)) {
            return redirect()->to(filingContinueUrl($filing));
        }

        $oldStatus = $filing->status;
        $filing->update(['status' => 'details_review']);
        logFilingStatus($filing->id, $oldStatus, 'details_review', Auth::id(), 'Documents uploaded — review details before payment');

        return redirect()->route('user.review-details', $filing)->with('success', 'Documents received. Review your details next.');
    }

    public function reviewDetails(ItrFiling $filing)
    {
        $this->authorizeFiling($filing);
        $docs = Document::where('filing_id', $filing->id)->orderByDesc('created_at')->get();
        $prefill = $this->decodePrefill($filing);
        $answers = $prefill['questionnaire'] ?? [];
        $questions = questionnaireQuestions();
        $plan = $filing->plan;
        $step = 'details';

        return view('user.review-details', compact('filing', 'docs', 'answers', 'questions', 'plan', 'step'));
    }

    public function confirmDetails(ItrFiling $filing)
    {
        $this->authorizeFiling($filing);

        if ($filing->filing_mode !== 'assisted') {
            return redirect()->route('user.summary', $filing);
        }

        if ($filing->status === 'payment_pending') {
            return redirect()->route('user.payment', $filing);
        }

        if ($filing->status !== 'details_review') {
            return redirect()->to(filingContinueUrl($filing))->with('info', 'Continue from your current step.');
        }

        $oldStatus = $filing->status;
        $filing->update(['status' => 'payment_pending']);
        logFilingStatus($filing->id, $oldStatus, 'payment_pending', Auth::id(), 'Details confirmed — proceed to payment');

        return redirect()->route('user.payment', $filing)->with('success', 'Details confirmed. Complete payment next.');
    }

    public function summary(ItrFiling $filing)
    {
        $this->authorizeFiling($filing);
        $step = 'summary';
        $prefill = $this->decodePrefill($filing);
        $tds = (float) ($prefill['tds_deducted'] ?? $filing->form16_tds ?? 0);
        $aisTds = (float) ($filing->ais_tds ?? $prefill['ais_tds'] ?? 0);
        $gross = (float) ($filing->gross_salary ?? 0);
        $ded = (float) ($filing->total_deductions ?? 0);
        $breakdown = computeTaxSummary($gross, $ded, $tds);
        $aisCheck = AisReconcile::compare($tds, $aisTds);

        return view('user.summary', compact('filing', 'step', 'prefill', 'breakdown', 'aisCheck', 'aisTds'));
    }

    public function saveSummary(Request $request, ItrFiling $filing)
    {
        $this->authorizeFiling($filing);

        $editable = ['summary_pending', 'customer_summary', 'ready_to_file'];
        if (! in_array($filing->status, $editable, true)) {
            return redirect()->to(filingContinueUrl($filing))->with('info', 'Tax summary is not editable at this stage.');
        }

        $data = $request->validate([
            'employer_name' => 'required|string|max:255',
            'gross_salary' => Validation::moneyRequired(),
            'total_deductions' => Validation::moneyRequired(),
            'tds_deducted' => Validation::moneyOptional(),
            'ais_tds' => Validation::moneyOptional(),
            'tax_regime' => 'required|in:old,new',
        ]);

        if ((float) $data['gross_salary'] <= 0) {
            return back()->withInput()->with('error', 'Gross salary / income must be greater than zero.');
        }

        $prefill = $this->decodePrefill($filing);
        $gross = (float) $data['gross_salary'];
        $ded = (float) $data['total_deductions'];
        $tds = (float) ($data['tds_deducted'] ?? 0);
        $aisTds = (float) ($data['ais_tds'] ?? 0);
        $regime = $data['tax_regime'];
        $summary = computeTaxSummary($gross, $ded, $tds);
        $oldStatus = $filing->status;
        if ($filing->filing_mode === 'self') {
            $next = 'ready_to_file';
        } elseif ($oldStatus === 'customer_summary') {
            // Save edits only — approve is a separate action.
            $next = 'customer_summary';
        } else {
            $next = 'payment_pending';
        }

        $filing->update([
            'gross_salary' => $summary['gross_salary'],
            'total_deductions' => $summary['total_deductions'],
            'form16_tds' => $tds,
            'ais_tds' => $aisTds,
            'tax_old_regime' => $summary['tax_old_regime'],
            'tax_new_regime' => $summary['tax_new_regime'],
            'tax_regime' => $regime,
            'status' => $next,
            'notes' => json_encode(array_merge($prefill, [
                'employer_name' => $data['employer_name'],
                'tds_deducted' => $summary['tds_deducted'],
                'ais_tds' => $aisTds,
                'payable_or_refund_old' => $summary['payable_or_refund_old'],
                'payable_or_refund_new' => $summary['payable_or_refund_new'],
                'taxable_old' => $summary['taxable_old'],
                'taxable_new' => $summary['taxable_new'],
                'standard_deduction_old' => $summary['standard_deduction_old'],
                'standard_deduction_new' => $summary['standard_deduction_new'],
            ])),
        ]);

        logFilingStatus($filing->id, $oldStatus, $next, Auth::id(), 'Regime selected: '.strtoupper($regime));

        if ($next === 'ready_to_file') {
            return redirect()->route('user.review', $filing)->with('success', 'Summary saved. Confirm declaration to generate your filing reference.');
        }

        if ($oldStatus === 'customer_summary') {
            return redirect()->route('user.summary', $filing)->with('success', 'Changes saved. Approve when you are ready.');
        }

        return redirect()->route('user.payment', $filing)->with('success', 'Summary saved. Complete payment to continue.');
    }

    public function approveSummary(Request $request, ItrFiling $filing)
    {
        $this->authorizeFiling($filing);

        if ($filing->status !== 'customer_summary') {
            return redirect()->to(filingContinueUrl($filing))->with('info', 'Nothing to approve right now.');
        }

        // Prefer posted form values; fall back to expert-prepared figures already on the filing.
        $prefill = $this->decodePrefill($filing);
        if ($request->filled('gross_salary') || $request->filled('employer_name')) {
            $data = $request->validate([
                'employer_name' => 'required|string|max:255',
                'gross_salary' => Validation::moneyRequired(),
                'total_deductions' => Validation::moneyRequired(),
                'tds_deducted' => Validation::moneyOptional(),
                'ais_tds' => Validation::moneyOptional(),
                'tax_regime' => 'required|in:old,new',
            ]);

            if ((float) $data['gross_salary'] <= 0) {
                return back()->withInput()->with('error', 'Gross salary / income must be greater than zero.');
            }

            $gross = (float) $data['gross_salary'];
            $ded = (float) $data['total_deductions'];
            $tds = (float) ($data['tds_deducted'] ?? 0);
            $aisTds = (float) ($data['ais_tds'] ?? 0);
            $regime = $data['tax_regime'];
            $employer = $data['employer_name'];
        } else {
            $gross = (float) $filing->gross_salary;
            $ded = (float) $filing->total_deductions;
            $tds = (float) ($prefill['tds_deducted'] ?? $filing->form16_tds ?? 0);
            $aisTds = (float) ($filing->ais_tds ?? $prefill['ais_tds'] ?? 0);
            $regime = in_array($filing->tax_regime, ['old', 'new'], true) ? $filing->tax_regime : 'new';
            $employer = (string) ($prefill['employer_name'] ?? 'Employer');

            if ($gross <= 0) {
                return redirect()->route('user.summary', $filing)->with('error', 'Review and save income figures before approving.');
            }
        }

        $summary = computeTaxSummary($gross, $ded, $tds);
        $oldStatus = $filing->status;

        $filing->update([
            'gross_salary' => $summary['gross_salary'],
            'total_deductions' => $summary['total_deductions'],
            'form16_tds' => $tds,
            'ais_tds' => $aisTds,
            'tax_old_regime' => $summary['tax_old_regime'],
            'tax_new_regime' => $summary['tax_new_regime'],
            'tax_regime' => $regime,
            'status' => 'customer_approved',
            'notes' => json_encode(array_merge($prefill, [
                'employer_name' => $employer,
                'tds_deducted' => $summary['tds_deducted'],
                'ais_tds' => $aisTds,
                'payable_or_refund_old' => $summary['payable_or_refund_old'],
                'payable_or_refund_new' => $summary['payable_or_refund_new'],
                'taxable_old' => $summary['taxable_old'],
                'taxable_new' => $summary['taxable_new'],
                'standard_deduction_old' => $summary['standard_deduction_old'],
                'standard_deduction_new' => $summary['standard_deduction_new'],
            ])),
        ]);

        logFilingStatus($filing->id, $oldStatus, 'customer_approved', Auth::id(), 'Customer approved tax summary');

        if ($filing->ca_id) {
        }

        return redirect()->route('user.track', $filing)->with('success', 'Approved. Waiting for your tax expert to file.');
    }

    public function review(ItrFiling $filing)
    {
        $this->authorizeFiling($filing);

        if ($filing->filing_mode !== 'self' && ! in_array($filing->status, ['ready_to_file', 'customer_approved', 'filed', 'completed'], true)) {
            return redirect()->route('user.track', $filing)->with('info', 'Expert filing — track status instead.');
        }

        $step = 'review';

        return view('user.review', compact('filing', 'step'));
    }

    public function selfFile(Request $request, ItrFiling $filing)
    {
        $this->authorizeFiling($filing);

        if ($filing->filing_mode !== 'self') {
            return redirect()->route('user.track', $filing)->with('error', 'Only self filing can use this action.');
        }

        if ($filing->status !== 'ready_to_file') {
            return redirect()->to(filingContinueUrl($filing))->with('error', 'Complete tax summary before filing.');
        }

        if ((float) $filing->gross_salary <= 0) {
            return redirect()->route('user.summary', $filing)->with('error', 'Enter valid income figures before filing.');
        }

        if (! Document::where('filing_id', $filing->id)->where('doc_type', 'form16')->exists()) {
            return redirect()->route('user.documents', $filing)->with('error', 'Form 16 is required before filing.');
        }

        $request->validate([
            'declaration' => 'accepted',
        ], [
            'declaration.accepted' => 'Please accept the declaration to file your ITR.',
        ]);

        $pan = $filing->pan ?: Auth::user()->pan;
        if (! $pan || ! preg_match('/^[A-Z]{5}[0-9]{4}[A-Z]$/i', $pan)) {
            return redirect()->route('user.complete-profile')->with('error', 'A valid PAN is required before filing.');
        }

        $ack = 'ITR'.date('Y').strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
        $oldStatus = $filing->status;

        $filing->update([
            'status' => 'filed',
            'acknowledgement_no' => $ack,
            'everify_status' => 'pending',
            'filed_at' => now(),
            'pan' => strtoupper($pan),
        ]);

        logFilingStatus($filing->id, $oldStatus, 'filed', Auth::id(), 'Self filing reference created: '.$ack);

        $content = "ITR Tax Acknowledgement\n"
            ."ACK No: {$ack}\n"
            ."Filing ID: {$filing->id}\n"
            .'PAN: '.strtoupper($pan)."\n"
            ."ITR Type: {$filing->itr_type}\n"
            .'Gross: '.$filing->gross_salary."\n"
            .'Regime: '.strtoupper($filing->tax_regime ?? 'new')."\n"
            .'Filed at: '.now()->toDateTimeString()."\n";
        $fname = 'ack_'.$filing->id.'.txt';
        Storage::disk('local')->put('receipts/'.$fname, $content);

        Receipt::create([
            'filing_id' => $filing->id,
            'uploaded_by' => Auth::id(),
            'acknowledgement_no' => $ack,
            'file_path' => 'receipts/'.$fname,
            'original_name' => 'ITR-Acknowledgement-'.$ack.'.txt',
        ]);

        $filing->update(['status' => 'completed']);
        logFilingStatus($filing->id, 'filed', 'completed', Auth::id(), 'Acknowledgement ready — e-verify on Income Tax portal');

        return redirect()->route('user.acknowledgement', $filing)->with('success', 'Filing reference generated: '.$ack.'. Complete e-filing/e-verification on the Income Tax portal as required.');
    }

    public function payment(ItrFiling $filing)
    {
        $this->authorizeFiling($filing);

        if ($filing->filing_mode !== 'assisted') {
            return redirect()->to(filingContinueUrl($filing));
        }

        $plan = $filing->plan;
        $payments = Payment::where('filing_id', $filing->id)->orderByDesc('created_at')->get();
        $coupons = Coupon::where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
            })
            ->orderBy('code')
            ->get();
        $step = 'pay';
        $razorpayLive = PaymentGateway::isLive();
        $razorpayKey = PaymentGateway::razorpayKey();

        return view('user.payment', compact('filing', 'plan', 'payments', 'coupons', 'step', 'razorpayLive', 'razorpayKey'));
    }

    public function processPayment(Request $request, ItrFiling $filing)
    {
        $this->authorizeFiling($filing);

        if ($filing->filing_mode !== 'assisted') {
            return redirect()->to(filingContinueUrl($filing))->with('error', 'Payment is only for expert-assisted filing.');
        }

        if ($filing->status !== 'payment_pending') {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Payment is not pending for this filing.'], 422);
            }

            return redirect()->route('user.track', $filing)->with('info', 'Payment is not pending for this filing.');
        }

        if (Payment::where('filing_id', $filing->id)->where('status', 'success')->exists()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Payment already completed.'], 422);
            }

            return redirect()->route('user.track', $filing)->with('info', 'Payment already completed.');
        }

        $data = $request->validate([
            'method' => 'required|in:upi,card,netbanking,razorpay',
            'coupon_code' => 'nullable|string|max:40',
            'razorpay_order_id' => 'nullable|string|max:100',
            'razorpay_payment_id' => 'nullable|string|max:100',
            'razorpay_signature' => 'nullable|string|max:255',
        ]);

        $amount = (float) ($filing->plan?->price ?? $filing->amount);
        if ($amount <= 0) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Invalid plan amount. Please contact support.'], 422);
            }

            return back()->with('error', 'Invalid plan amount. Please contact support.');
        }

        $discount = 0;
        $couponCode = strtoupper(trim((string) ($data['coupon_code'] ?? '')));
        $couponId = null;
        $coupon = null;

        if ($couponCode !== '') {
            $coupon = Coupon::where('code', $couponCode)->where('is_active', true)->first();
            $couponError = null;
            if (! $coupon) {
                $couponError = 'Coupon code not found.';
            } elseif ($coupon->expires_at && $coupon->expires_at->isPast()) {
                $couponError = 'Coupon has expired.';
            } elseif ($coupon->max_uses > 0 && $coupon->used_count >= $coupon->max_uses) {
                $couponError = 'Coupon usage limit reached.';
            } elseif ($amount < (float) $coupon->min_amount) {
                $couponError = 'Order amount is below coupon minimum of '.money($coupon->min_amount).'.';
            }
            if ($couponError) {
                if ($request->expectsJson()) {
                    return response()->json(['message' => $couponError], 422);
                }

                return back()->withInput()->with('error', $couponError);
            }
            $discount = $coupon->type === 'percent'
                ? round($amount * $coupon->value / 100, 2)
                : (float) $coupon->value;
            $discount = min($discount, $amount);
            $couponId = $coupon->id;
        }

        $final = max(0, $amount - $discount);
        $method = $data['method'];
        $txn = 'TXN'.strtoupper(bin2hex(random_bytes(6)));

        if (PaymentGateway::isLive()) {
            if (! $request->filled('razorpay_payment_id')) {
                $order = PaymentGateway::createOrder($final, 'filing-'.$filing->id, [
                    'filing_id' => (string) $filing->id,
                    'user_id' => (string) Auth::id(),
                ]);

                if (! $order || empty($order['id'])) {
                    if ($request->expectsJson()) {
                        return response()->json(['message' => 'Could not start Razorpay checkout. Check keys in Settings or pay in demo mode without keys.'], 422);
                    }

                    return back()->withInput()->with('error', 'Could not start Razorpay checkout. Check keys in Settings or pay in demo mode without keys.');
                }

                return response()->json([
                    'order_id' => $order['id'],
                    'amount' => $order['amount'],
                    'currency' => $order['currency'],
                    'key' => PaymentGateway::razorpayKey(),
                    'name' => config('itr.name', 'ITR Tax'),
                    'description' => 'Filing #'.$filing->id.' · '.($filing->plan?->name ?? 'Expert plan'),
                    'prefill' => [
                        'name' => Auth::user()->name,
                        'email' => Auth::user()->email,
                        'contact' => Auth::user()->phone,
                    ],
                ]);
            }

            $orderId = (string) $data['razorpay_order_id'];
            $paymentId = (string) $data['razorpay_payment_id'];
            $signature = (string) $data['razorpay_signature'];

            if (! PaymentGateway::verifySignature($orderId, $paymentId, $signature)) {
                return back()->withInput()->with('error', 'Razorpay signature verification failed.');
            }

            $txn = $paymentId;
            $method = 'razorpay';
        }

        Payment::create([
            'filing_id' => $filing->id,
            'user_id' => Auth::id(),
            'amount' => $final,
            'discount' => $discount,
            'coupon_code' => $couponCode ?: null,
            'method' => $method,
            'transaction_id' => $txn,
            'status' => 'success',
            'paid_at' => now(),
        ]);

        if ($coupon) {
            $coupon->increment('used_count');
        }

        $oldStatus = $filing->status;
        $filing->update([
            'status' => 'paid',
            'ca_id' => null,
            'coupon_id' => $couponId,
            'discount_amount' => $discount,
            'amount' => $final,
        ]);
        logFilingStatus($filing->id, $oldStatus, 'paid', Auth::id(), 'Payment successful · '.$txn.' · '.$method);

        $expert = ExpertAssigner::pickAvailable();
        if ($expert) {
            ExpertAssigner::assign($filing->fresh(), $expert, null, 'Auto-assigned after payment');

            return redirect()->route('user.track', $filing)->with('success', 'Payment successful. Tax expert '.$expert->name.' has been assigned.');
        }


        return redirect()->route('user.track', $filing)->with('success', 'Payment successful. Waiting for admin to assign your tax expert.');
    }

    public function cancelFiling(Request $request, ItrFiling $filing)
    {
        $this->authorizeFiling($filing);

        if (in_array($filing->status, ['filed', 'completed', 'cancelled'], true)) {
            return back()->with('error', 'This filing can no longer be cancelled.');
        }

        if (Payment::where('filing_id', $filing->id)->where('status', 'success')->exists()) {
            return back()->with('error', 'Paid filings cannot be cancelled here. Contact support for a refund review.');
        }

        $oldStatus = $filing->status;
        $filing->update(['status' => 'cancelled']);
        logFilingStatus($filing->id, $oldStatus, 'cancelled', Auth::id(), 'Cancelled by customer');

        return redirect()->route('user.track-list')->with('success', 'Filing #'.$filing->id.' cancelled.');
    }

    public function upgradeToAssisted(Request $request, ItrFiling $filing)
    {
        $this->authorizeFiling($filing);

        if ($filing->filing_mode !== 'self') {
            return back()->with('info', 'This filing is already on expert-assisted mode.');
        }

        if (in_array($filing->status, ['filed', 'completed', 'cancelled'], true)) {
            return back()->with('error', 'This filing cannot be converted.');
        }

        $data = $request->validate([
            'plan_id' => 'required|integer|exists:plans,id',
        ]);

        $plan = Plan::where('id', $data['plan_id'])->where('is_active', true)->where('slug', '!=', 'self-free')->first();
        if (! $plan) {
            return back()->with('error', 'Please select a valid expert plan.');
        }

        $oldStatus = $filing->status;
        $next = match (true) {
            in_array($oldStatus, ['ready_to_file', 'summary_pending'], true) => 'payment_pending',
            $oldStatus === 'documents_pending' => 'documents_pending',
            $oldStatus === 'details_review' => 'details_review',
            $oldStatus === 'questionnaire_pending', $oldStatus === 'draft' => 'questionnaire_pending',
            default => $oldStatus === 'payment_pending' ? 'payment_pending' : $oldStatus,
        };

        // If docs already done but not yet at payment, send through confirm → pay.
        if (in_array($oldStatus, ['summary_pending', 'ready_to_file'], true)) {
            $hasForm16 = Document::where('filing_id', $filing->id)->where('doc_type', 'form16')->exists();
            $next = $hasForm16 ? 'payment_pending' : 'documents_pending';
        }

        $filing->update([
            'filing_mode' => 'assisted',
            'plan_id' => $plan->id,
            'amount' => (float) $plan->price,
            'status' => $next,
        ]);

        logFilingStatus($filing->id, $oldStatus, $next, Auth::id(), 'Converted Self → Expert ('.$plan->name.')');

        if ($next === 'payment_pending') {
            return redirect()->route('user.payment', $filing)->with('success', 'Upgraded to expert assistance. Complete payment to get an expert assigned.');
        }

        return redirect()->to(filingContinueUrl($filing->fresh()))->with('success', 'Upgraded to expert assistance. Continue — payment comes after you confirm details.');
    }

    public function markEverified(ItrFiling $filing)
    {
        $this->authorizeFiling($filing);

        if (! filled($filing->acknowledgement_no)) {
            return back()->with('error', 'Acknowledgement is required before marking e-verify.');
        }

        $filing->update([
            'everify_status' => 'verified',
            'everified_at' => now(),
        ]);

        logFilingStatus($filing->id, $filing->status, $filing->status, Auth::id(), 'Customer marked e-verify complete (local record — confirm on Income Tax portal)');

        return back()->with('success', 'E-verify marked complete in ITR Tax. Keep your Income Tax portal confirmation as the official record.');
    }

    public function trackList()
    {
        $filings = ItrFiling::with('plan')
            ->where('user_id', Auth::id())
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        return view('user.track-list', compact('filings'));
    }

    public function track(ItrFiling $filing)
    {
        $this->authorizeFiling($filing);

        $logs = StatusLog::with('changedBy')->where('filing_id', $filing->id)->orderBy('created_at')->get();
        $notes = Note::with('author')
            ->where('filing_id', $filing->id)
            ->where('is_internal', false)
            ->orderByDesc('created_at')
            ->get();
        $ca = $filing->ca_id ? User::select('name', 'email', 'phone')->find($filing->ca_id) : null;
        $step = 'expert';
        $upgradePlans = Plan::where('is_active', true)->where('slug', '!=', 'self-free')->orderBy('sort_order')->get();
        $hasPaid = Payment::where('filing_id', $filing->id)->where('status', 'success')->exists();

        return view('user.track', compact('filing', 'logs', 'notes', 'ca', 'step', 'upgradePlans', 'hasPaid'));
    }

    public function acknowledgement(ItrFiling $filing)
    {
        $this->authorizeFiling($filing);
        $receipt = Receipt::where('filing_id', $filing->id)->orderByDesc('created_at')->first();
        $step = 'done';

        return view('user.acknowledgement', compact('filing', 'receipt', 'step'));
    }

    public function downloadReceipt(ItrFiling $filing)
    {
        $this->authorizeFiling($filing);
        $receipt = Receipt::where('filing_id', $filing->id)->orderByDesc('created_at')->first();

        if (! $receipt || ! Storage::disk('local')->exists($receipt->file_path)) {
            return redirect()->route('user.acknowledgement', $filing)->with('error', 'Acknowledgement not available yet.');
        }

        return Storage::disk('local')->download($receipt->file_path, $receipt->original_name ?: 'acknowledgement.txt');
    }

    public function profile()
    {
        return view('user.profile', ['user' => Auth::user()]);
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => Validation::phoneOptional(),
            'pan' => Validation::panOptional(),
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'pincode' => Validation::pincodeOptional(),
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        $user->update([
            'name' => $data['name'],
            'phone' => $data['phone'] ?? null,
        ]);

        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'pan' => isset($data['pan']) ? strtoupper($data['pan']) : $user->pan,
                'address' => $data['address'] ?? null,
                'city' => $data['city'] ?? null,
                'state' => $data['state'] ?? null,
                'pincode' => $data['pincode'] ?? null,
            ]
        );

        if (! empty($data['password'])) {
            $user->update(['password' => $data['password']]);
        }

        return redirect()->route('user.profile')->with('success', 'Profile updated successfully.');
    }

    private function authorizeFiling(ItrFiling $filing): void
    {
        if ($filing->user_id !== Auth::id()) {
            abort(403);
        }
    }

    private function decodePrefill(ItrFiling $filing): array
    {
        if (! $filing->notes) {
            return [];
        }
        $decoded = json_decode($filing->notes, true);

        return is_array($decoded) ? $decoded : [];
    }
}
