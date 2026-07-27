<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentRequest;
use App\Models\ItrFiling;
use App\Models\Note;
use App\Models\Receipt;
use App\Models\StatusLog;
use App\Models\User;
use App\Support\Validation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CaController extends Controller
{
    public function dashboard()
    {
        $caId = Auth::id();
        $stats = [
            'assigned' => ItrFiling::where('ca_id', $caId)->count(),
            'review' => ItrFiling::where('ca_id', $caId)->whereIn('status', ['assigned', 'under_review', 'docs_requested', 'customer_summary', 'customer_approved'])->count(),
            'filed' => ItrFiling::where('ca_id', $caId)->whereIn('status', ['filed', 'completed'])->count(),
            'pending_docs' => DocumentRequest::where('ca_id', $caId)->where('status', 'open')->count(),
        ];

        $clients = ItrFiling::with(['user', 'plan'])
            ->where('ca_id', $caId)
            ->orderByDesc('updated_at')
            ->limit(10)
            ->get();

        return view('ca.dashboard', compact('stats', 'clients'));
    }

    public function clients(Request $request)
    {
        $query = ItrFiling::with(['user', 'plan'])->where('ca_id', Auth::id());

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $clients = $query->orderByDesc('updated_at')->paginate(10)->withQueryString();
        $filter = $request->status;

        return view('ca.clients', compact('clients', 'filter'));
    }

    public function showFiling(ItrFiling $filing)
    {
        $this->authorizeCaFiling($filing);

        $client = User::find($filing->user_id);
        $docs = Document::where('filing_id', $filing->id)->orderByDesc('created_at')->get();
        $notes = Note::with('author')->where('filing_id', $filing->id)->orderByDesc('created_at')->get();
        $requests = DocumentRequest::where('filing_id', $filing->id)->orderByDesc('created_at')->get();
        $receipt = Receipt::where('filing_id', $filing->id)->orderByDesc('id')->first();
        $logs = StatusLog::where('filing_id', $filing->id)->orderBy('created_at')->get();

        return view('ca.filing', compact('filing', 'client', 'docs', 'notes', 'requests', 'receipt', 'logs'));
    }

    public function addNote(Request $request, ItrFiling $filing)
    {
        $this->authorizeCaFiling($filing);

        $request->validate(['note' => 'required|string']);

        $isInternal = $request->input('is_internal') === '1';

        Note::create([
            'filing_id' => $filing->id,
            'author_id' => Auth::id(),
            'note' => $request->note,
            'is_internal' => $isInternal,
        ]);

        if (! $isInternal) {
        }

        return back()->with('success', 'Note added.');
    }

    public function requestDocuments(Request $request, ItrFiling $filing)
    {
        $this->authorizeCaFiling($filing);

        if (! in_array($filing->status, ['assigned', 'under_review', 'docs_requested'], true)) {
            return back()->with('error', 'You can request documents only while the filing is under expert review.');
        }

        $request->validate(['message' => 'required|string']);

        DocumentRequest::create([
            'filing_id' => $filing->id,
            'ca_id' => Auth::id(),
            'message' => $request->message,
            'required_docs' => $request->input('required_docs'),
            'status' => 'open',
        ]);

        $oldStatus = $filing->status;
        $filing->update(['status' => 'docs_requested']);

        logFilingStatus($filing->id, $oldStatus, 'docs_requested', Auth::id(), $request->message);

        return back()->with('success', 'Document request sent to client.');
    }

    public function startReview(ItrFiling $filing)
    {
        $this->authorizeCaFiling($filing);

        if (! in_array($filing->status, ['assigned', 'paid', 'docs_requested'], true)) {
            return back()->with('error', 'This filing is not ready to start review.');
        }

        $oldStatus = $filing->status;
        $filing->update(['status' => 'under_review']);

        logFilingStatus($filing->id, $oldStatus, 'under_review', Auth::id(), 'Tax expert started review');

        return back()->with('success', 'Marked as under review.');
    }

    public function markFiled(Request $request, ItrFiling $filing)
    {
        $this->authorizeCaFiling($filing);

        // Expert flow: after review send summary to customer; file only after customer_approved
        if (! in_array($filing->status, ['customer_approved', 'ready_to_file'], true)) {
            return back()->with('error', 'Wait for the customer to approve the tax summary before filing.');
        }

        $data = $request->validate([
            'acknowledgement_no' => Validation::ackNo(),
        ]);

        $oldStatus = $filing->status;
        $filing->update([
            'status' => 'filed',
            'acknowledgement_no' => strtoupper($data['acknowledgement_no']),
            'everify_status' => 'pending',
            'filed_at' => now(),
        ]);

        logFilingStatus($filing->id, $oldStatus, 'filed', Auth::id(), 'ITR filed. ACK: '.$request->acknowledgement_no);

        return back()->with('success', 'Filing marked as filed.');
    }

    public function sendSummary(Request $request, ItrFiling $filing)
    {
        $this->authorizeCaFiling($filing);

        if (! in_array($filing->status, ['under_review', 'assigned', 'docs_requested'], true)) {
            return back()->with('error', 'Start review before sending the tax summary.');
        }

        $data = $request->validate([
            'gross_salary' => Validation::moneyRequired(),
            'total_deductions' => Validation::moneyRequired(),
            'tds_deducted' => Validation::moneyOptional(),
            'tax_regime' => 'required|in:old,new',
            'expert_note' => 'nullable|string|max:2000',
        ]);

        if ((float) $data['gross_salary'] <= 0) {
            return back()->withInput()->with('error', 'Gross salary / income must be greater than zero.');
        }

        $gross = (float) $data['gross_salary'];
        $ded = (float) $data['total_deductions'];
        $tds = (float) ($data['tds_deducted'] ?? 0);
        $summary = computeTaxSummary($gross, $ded, $tds);
        $regime = $data['tax_regime'];
        $prefill = [];
        if ($filing->notes) {
            $decoded = json_decode($filing->notes, true);
            $prefill = is_array($decoded) ? $decoded : [];
        }

        $oldStatus = $filing->status;
        $filing->update([
            'status' => 'customer_summary',
            'gross_salary' => $summary['gross_salary'],
            'total_deductions' => $summary['total_deductions'],
            'tax_old_regime' => $summary['tax_old_regime'],
            'tax_new_regime' => $summary['tax_new_regime'],
            'tax_regime' => $regime,
            'notes' => json_encode(array_merge($prefill, [
                'tds_deducted' => $summary['tds_deducted'],
                'payable_or_refund_old' => $summary['payable_or_refund_old'],
                'payable_or_refund_new' => $summary['payable_or_refund_new'],
                'taxable_old' => $summary['taxable_old'],
                'taxable_new' => $summary['taxable_new'],
                'expert_summary_note' => $data['expert_note'] ?? null,
            ])),
        ]);

        logFilingStatus($filing->id, $oldStatus, 'customer_summary', Auth::id(), 'Tax summary sent to customer for approval');

        return back()->with('success', 'Tax summary sent to customer for approval.');
    }

    public function uploadReceipt(Request $request, ItrFiling $filing)
    {
        $this->authorizeCaFiling($filing);

        if (! in_array($filing->status, ['filed', 'completed', 'customer_approved', 'ready_to_file'], true)
            && ! filled($filing->acknowledgement_no)) {
            return back()->with('error', 'Mark the return as filed (with ACK) before uploading the receipt file.');
        }

        $request->validate(['receipt' => 'required|file|max:10240']);

        $uploaded = uploadItrFile($request->file('receipt'), 'receipts');
        if (! $uploaded) {
            return back()->with('error', 'Upload failed.');
        }

        $ack = $request->input('acknowledgement_no') ?: $filing->acknowledgement_no;

        Receipt::create([
            'filing_id' => $filing->id,
            'uploaded_by' => Auth::id(),
            'acknowledgement_no' => $ack,
            'file_path' => $uploaded['file_path'],
            'original_name' => $uploaded['original_name'],
        ]);

        $oldStatus = $filing->status;
        $filing->update([
            'status' => 'completed',
            'acknowledgement_no' => $ack,
        ]);

        logFilingStatus($filing->id, $oldStatus, 'completed', Auth::id(), 'ITR receipt uploaded');

        return back()->with('success', 'Receipt uploaded. Filing completed.');
    }

    public function downloadDoc(Document $doc): StreamedResponse
    {
        $filing = ItrFiling::findOrFail($doc->filing_id);
        $this->authorizeCaFiling($filing);

        if (! Storage::disk('local')->exists($doc->file_path)) {
            abort(404);
        }

        return Storage::disk('local')->download($doc->file_path, $doc->original_name);
    }

    private function authorizeCaFiling(ItrFiling $filing): void
    {
        if ((int) $filing->ca_id !== Auth::id()) {
            abort(403, 'Filing not assigned to you.');
        }
    }
}
