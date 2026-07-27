<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\Faq;
use App\Models\ItrFiling;
use App\Models\Plan;
use App\Models\SupportTicket;
use App\Models\User;
use App\Support\HraCalculator;
use App\Support\Validation;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $plans = Plan::where('is_active', true)->where('slug', '!=', 'self-free')->orderBy('sort_order')->get();
        $faqs = Faq::where('is_active', true)->orderBy('sort_order')->limit(6)->get();
        $blogs = Blog::where('is_published', true)->orderByDesc('published_at')->limit(3)->get();
        $stats = [
            'filings' => ItrFiling::count(),
            'completed' => ItrFiling::whereIn('status', ['filed', 'completed'])->count(),
            'experts' => User::withRole('ca')->where('status', 'active')->count(),
            'users' => User::withRole('user')->where('status', 'active')->count(),
        ];
        $processBoth = processSteps('both');
        $processSelf = processSteps('self');
        $processAssisted = processSteps('assisted');

        return view('public.home', compact('plans', 'faqs', 'blogs', 'stats', 'processBoth', 'processSelf', 'processAssisted'));
    }

    public function efiling()
    {
        $plans = Plan::where('is_active', true)->where('slug', '!=', 'self-free')->orderBy('sort_order')->limit(3)->get();
        $faqs = Faq::where('is_active', true)->orderBy('sort_order')->limit(6)->get();
        $processBoth = processSteps('both');

        return view('public.efiling', compact('plans', 'faqs', 'processBoth'));
    }

    public function pricing()
    {
        $plans = Plan::where('is_active', true)->where('slug', '!=', 'self-free')->orderBy('sort_order')->get();

        return view('public.pricing', compact('plans'));
    }

    public function howItWorks()
    {
        $processSelf = processSteps('self');
        $processAssisted = processSteps('assisted');

        return view('public.how-it-works', compact('processSelf', 'processAssisted'));
    }

    public function taxCalculator()
    {
        return view('public.tax-calculator');
    }

    public function tools()
    {
        return view('public.tools');
    }

    public function hraCalculator()
    {
        return view('public.hra-calculator', ['result' => null]);
    }

    public function hraCalculatorCompute(Request $request)
    {
        $data = $request->validate([
            'basic' => 'required|numeric|min:0',
            'hra_received' => 'required|numeric|min:0',
            'rent_paid' => 'required|numeric|min:0',
            'metro' => 'nullable|in:0,1',
        ]);

        $result = HraCalculator::compute(
            (float) $data['basic'],
            (float) $data['hra_received'],
            (float) $data['rent_paid'],
            (string) ($data['metro'] ?? '0') === '1'
        );

        return view('public.hra-calculator', [
            'result' => $result,
            'input' => $data,
        ]);
    }

    public function rentReceipt()
    {
        return view('public.rent-receipt', ['receipt' => null]);
    }

    public function rentReceiptGenerate(Request $request)
    {
        $data = $request->validate([
            'tenant_name' => 'required|string|max:255',
            'landlord_name' => 'required|string|max:255',
            'landlord_pan' => 'nullable|string|max:10',
            'property_address' => 'required|string|max:500',
            'month' => 'required|string|max:40',
            'amount' => 'required|numeric|min:1',
            'city' => 'nullable|string|max:100',
        ]);

        return view('public.rent-receipt', [
            'receipt' => $data,
        ]);
    }

    public function refundStatus()
    {
        return view('public.refund-status');
    }

    public function refundStatusCheck(Request $request)
    {
        $data = $request->validate([
            'pan' => Validation::pan(),
            'acknowledgement_no' => Validation::ackNo(),
        ]);

        $pan = strtoupper($data['pan']);
        $ack = strtoupper(trim($data['acknowledgement_no']));

        $filing = ItrFiling::query()
            ->whereRaw('UPPER(pan) = ?', [$pan])
            ->whereRaw('UPPER(acknowledgement_no) = ?', [$ack])
            ->first();

        if (! $filing) {
            return back()
                ->withInput()
                ->with('error', 'No filing found for this PAN and acknowledgement number on ITR Tax.');
        }

        $labels = config('itr.status_labels', []);
        $label = $labels[$filing->status] ?? ucwords(str_replace('_', ' ', $filing->status));

        return back()->with('success', 'Filing #'.$filing->id.' · Status: '.$label.' · ITR '.$filing->itr_type.' · Filed: '.($filing->filed_at?->format('d M Y') ?: '—').'. Continue e-verify / refund on the Income Tax e-filing portal.');
    }

    public function about()
    {
        return view('public.about');
    }

    public function privacy()
    {
        return view('public.privacy');
    }

    public function terms()
    {
        return view('public.terms');
    }

    public function blogs()
    {
        $blogs = Blog::with('author')
            ->where('is_published', true)
            ->orderByDesc('published_at')
            ->paginate(10)
            ->withQueryString();

        return view('public.blogs', compact('blogs'));
    }

    public function blogShow(string $slug)
    {
        $blog = Blog::with('author')
            ->where('slug', $slug)
            ->where('is_published', true)
            ->first();

        if (! $blog) {
            abort(404);
        }

        return view('public.blog-show', compact('blog'));
    }

    public function faqs()
    {
        $faqs = Faq::where('is_active', true)->orderBy('sort_order')->get();

        return view('public.faqs', compact('faqs'));
    }

    public function contact()
    {
        return view('public.contact');
    }

    public function contactSubmit(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => Validation::phoneOptional(),
            'message' => 'required|string|min:10|max:5000',
        ]);

        $ticketPayload = [
            'user_id' => auth()->id(),
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'subject' => 'Website contact from '.$data['name'],
            'message' => $data['message'],
            'status' => 'open',
            'priority' => 'normal',
        ];

        try {
            $ticket = SupportTicket::create($ticketPayload);
        } catch (\Throwable) {
            // Older DB without nullable user_id / contact columns: notify admins only.
            $ticket = null;

            return redirect()->route('contact')->with('success', 'Thanks! Your message was submitted. We will get back to you shortly.');
        }

        return redirect()->route('contact')->with('success', 'Thanks! Your message was submitted. We will get back to you shortly.');
    }
}
