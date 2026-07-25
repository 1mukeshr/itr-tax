<?php

namespace App\Http\Controllers;

use App\Models\ItrFiling;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use App\Models\UserProfile;
use App\Support\ExpertAssigner;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    /** Complete ITR = filed on portal or marked completed (ACK done). */
    private function completedStatuses(): array
    {
        return ['filed', 'completed'];
    }

    /** In-progress filings (not cancelled, not complete). */
    private function pendingStatuses(): array
    {
        return [
            'draft', 'questionnaire_pending', 'documents_pending', 'details_review',
            'summary_pending', 'payment_pending', 'paid', 'assigned', 'under_review',
            'docs_requested', 'customer_summary', 'customer_approved', 'ready_to_file',
        ];
    }

    public function dashboard(Request $request)
    {
        $paid = fn () => Payment::query()->where('status', 'success');
        $completedStatuses = $this->completedStatuses();
        $pendingStatuses = $this->pendingStatuses();

        // Customers only (role user) — not admins or tax experts.
        $customers = fn () => User::withRole('user');
        // Complete ITR dated by filed_at when set, else updated_at.
        $completedFilings = fn () => ItrFiling::query()
            ->whereIn('status', $completedStatuses);
        $completedAt = 'COALESCE(filed_at, updated_at)';

        $stats = [
            'all_orders' => ItrFiling::count(),
            'completed_orders' => $completedFilings()->count(),
            'completed_today' => $completedFilings()->whereRaw("{$completedAt} >= ?", [now()->startOfDay()])->count(),
            'completed_week' => $completedFilings()->whereRaw("{$completedAt} >= ?", [now()->startOfWeek()])->count(),
            'completed_month' => $completedFilings()->whereRaw("{$completedAt} >= ?", [now()->startOfMonth()])->count(),
            'pending_itr' => ItrFiling::whereIn('status', $pendingStatuses)->count(),
            'cancelled' => ItrFiling::where('status', 'cancelled')->count(),
            'revenue' => $paid()->sum('amount'),
            'payments_count' => $paid()->count(),
            'month_revenue' => $paid()->whereRaw('COALESCE(paid_at, created_at) >= ?', [now()->startOfMonth()])->sum('amount'),
            'week_revenue' => $paid()->whereRaw('COALESCE(paid_at, created_at) >= ?', [now()->startOfWeek()])->sum('amount'),
            'today_revenue' => $paid()->whereRaw('COALESCE(paid_at, created_at) >= ?', [now()->startOfDay()])->sum('amount'),
            'users' => $customers()->count(),
            'users_today' => $customers()->whereDate('created_at', today())->count(),
            'users_week' => $customers()->where('created_at', '>=', now()->startOfWeek())->count(),
            'users_month' => $customers()->where('created_at', '>=', now()->startOfMonth())->count(),
            'cas' => User::withRole('ca')->count(),
            'need_expert' => ItrFiling::where('status', 'paid')->where(function ($q) {
                $q->whereNull('ca_id')->orWhere('ca_id', 0);
            })->count(),
        ];

        $stats['avg_ticket'] = $stats['payments_count'] > 0
            ? round((float) $stats['revenue'] / $stats['payments_count'], 2)
            : 0;
        $stats['completion_rate'] = $stats['all_orders'] > 0
            ? round(($stats['completed_orders'] / $stats['all_orders']) * 100, 1)
            : 0;

        $paymentCharts = $this->paymentChartSeries();

        $orderFilter = $request->input('orders', 'all');
        if (! in_array($orderFilter, ['all', 'pending', 'completed'], true)) {
            $orderFilter = 'all';
        }

        $ordersQuery = ItrFiling::with(['user', 'plan', 'ca'])->orderByDesc('updated_at');
        if ($orderFilter === 'pending') {
            $ordersQuery->whereIn('status', $pendingStatuses);
        } elseif ($orderFilter === 'completed') {
            $ordersQuery->whereIn('status', $completedStatuses);
        }

        $orders = $ordersQuery->limit(20)->get();
        $needsExpert = $stats['need_expert'];

        return view('admin.dashboard', compact(
            'stats',
            'orders',
            'orderFilter',
            'needsExpert',
            'paymentCharts'
        ));
    }

    /** Day / week / month: revenue, payments, orders, new users, complete ITR. */
    private function paymentChartSeries(): array
    {
        $rows = Payment::query()
            ->where('status', 'success')
            ->whereRaw('COALESCE(paid_at, created_at) >= ?', [now()->subMonths(11)->startOfMonth()])
            ->get(['amount', 'paid_at', 'created_at']);

        $dated = $rows->map(function (Payment $p) {
            $at = $p->paid_at ?? $p->created_at;

            return [
                'at' => $at instanceof Carbon ? $at->copy() : Carbon::parse($at),
                'amount' => (float) $p->amount,
            ];
        });

        $userDated = User::withRole('user')
            ->where('created_at', '>=', now()->subMonths(11)->startOfMonth())
            ->get(['created_at'])
            ->map(function (User $u) {
                return [
                    'at' => $u->created_at instanceof Carbon ? $u->created_at->copy() : Carbon::parse($u->created_at),
                ];
            });

        $orderDated = ItrFiling::query()
            ->where('created_at', '>=', now()->subMonths(11)->startOfMonth())
            ->get(['created_at'])
            ->map(function (ItrFiling $f) {
                return [
                    'at' => $f->created_at instanceof Carbon ? $f->created_at->copy() : Carbon::parse($f->created_at),
                ];
            });

        $doneDated = ItrFiling::query()
            ->whereIn('status', $this->completedStatuses())
            ->whereRaw('COALESCE(filed_at, updated_at) >= ?', [now()->subMonths(11)->startOfMonth()])
            ->get(['filed_at', 'updated_at'])
            ->map(function (ItrFiling $f) {
                $at = $f->filed_at ?? $f->updated_at;

                return [
                    'at' => $at instanceof Carbon ? $at->copy() : Carbon::parse($at),
                ];
            });

        $bucket = function (Carbon $start, Carbon $end) use ($dated, $userDated, $orderDated, $doneDated): array {
            $slice = $dated->filter(fn ($r) => $r['at']->betweenIncluded($start, $end));
            $uSlice = $userDated->filter(fn ($r) => $r['at']->betweenIncluded($start, $end));
            $oSlice = $orderDated->filter(fn ($r) => $r['at']->betweenIncluded($start, $end));
            $dSlice = $doneDated->filter(fn ($r) => $r['at']->betweenIncluded($start, $end));

            return [
                'revenue' => round($slice->sum('amount'), 2),
                'count' => $slice->count(),
                'orders' => $oSlice->count(),
                'users' => $uSlice->count(),
                'completed' => $dSlice->count(),
            ];
        };

        $day = ['labels' => [], 'revenue' => [], 'count' => [], 'orders' => [], 'users' => [], 'completed' => []];
        for ($i = 13; $i >= 0; $i--) {
            $start = now()->subDays($i)->startOfDay();
            $end = $start->copy()->endOfDay();
            $b = $bucket($start, $end);
            $day['labels'][] = $start->format('d M');
            $day['revenue'][] = $b['revenue'];
            $day['count'][] = $b['count'];
            $day['orders'][] = $b['orders'];
            $day['users'][] = $b['users'];
            $day['completed'][] = $b['completed'];
        }

        $week = ['labels' => [], 'revenue' => [], 'count' => [], 'orders' => [], 'users' => [], 'completed' => []];
        for ($i = 7; $i >= 0; $i--) {
            $start = now()->startOfWeek()->subWeeks($i);
            $end = $start->copy()->endOfWeek();
            $b = $bucket($start, $end);
            $week['labels'][] = $start->format('d M').' - '.$end->format('d M');
            $week['revenue'][] = $b['revenue'];
            $week['count'][] = $b['count'];
            $week['orders'][] = $b['orders'];
            $week['users'][] = $b['users'];
            $week['completed'][] = $b['completed'];
        }

        $month = ['labels' => [], 'revenue' => [], 'count' => [], 'orders' => [], 'users' => [], 'completed' => []];
        for ($i = 11; $i >= 0; $i--) {
            $start = now()->subMonths($i)->startOfMonth();
            $end = $start->copy()->endOfMonth();
            $b = $bucket($start, $end);
            $month['labels'][] = $start->format('M Y');
            $month['revenue'][] = $b['revenue'];
            $month['count'][] = $b['count'];
            $month['orders'][] = $b['orders'];
            $month['users'][] = $b['users'];
            $month['completed'][] = $b['completed'];
        }

        return compact('day', 'week', 'month');
    }

    public function users(Request $request)
    {
        $role = $request->input('role', 'all');

        // Tax experts managed on dedicated page.
        if ($role === 'ca') {
            return redirect()->route('admin.cas');
        }

        $query = User::query();

        if (in_array($role, ['user', 'admin'], true)) {
            $query->withRole($role);
        } elseif ($role === 'all') {
            $query->whereHas('roleRelation', fn ($q) => $q->whereIn('slug', ['user', 'admin']));
        }

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($b) use ($q) {
                $b->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%")
                    ->orWhereHas('profile', fn ($p) => $p->where('pan', 'like', "%{$q}%"));
            });
        }

        $users = $query
            ->orderBy('role_id')
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();
        $counts = [
            'user' => User::withRole('user')->count(),
            'admin' => User::withRole('admin')->count(),
        ];
        $counts['all'] = $counts['user'] + $counts['admin'];

        return view('admin.users', [
            'users' => $users,
            'q' => $request->q,
            'role' => $role === 'ca' ? 'all' : $role,
            'counts' => $counts,
        ]);
    }

    public function toggleUser(User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'You cannot suspend your own admin account.');
        }

        $user->update(['status' => $user->status === 'active' ? 'suspended' : 'active']);

        return back()->with('success', roleLabel($user->role).' status updated.');
    }

    public function cas()
    {
        $cas = User::withRole('ca')
            ->leftJoin('user_profiles', 'user_profiles.user_id', '=', 'users.id')
            ->select('users.*', 'user_profiles.membership_no', 'user_profiles.specialization', 'user_profiles.experience_years', 'user_profiles.is_available', 'user_profiles.max_clients')
            ->selectSub(
                ItrFiling::selectRaw('count(*)')->whereColumn('ca_id', 'users.id'),
                'client_count'
            )
            ->orderByDesc('users.created_at')
            ->paginate(10)
            ->withQueryString();

        return view('admin.cas', compact('cas'));
    }

    public function createCa()
    {
        return view('admin.ca-form', ['ca' => null]);
    }

    public function storeCa(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:6',
            'membership_no' => 'nullable|string|max:50',
            'specialization' => 'nullable|string|max:255',
            'experience_years' => 'nullable|integer|min:0',
            'max_clients' => 'nullable|integer|min:1',
            'bio' => 'nullable|string',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => strtolower($data['email']),
            'phone' => $data['phone'] ?? null,
            'password' => $data['password'],
            'role_id' => Role::idFor('ca'),
            'status' => 'active',
        ]);

        UserProfile::create([
            'user_id' => $user->id,
            'membership_no' => $data['membership_no'] ?? null,
            'specialization' => $data['specialization'] ?? 'General ITR',
            'experience_years' => $data['experience_years'] ?? 0,
            'max_clients' => $data['max_clients'] ?? 50,
            'bio' => $data['bio'] ?? null,
            'is_available' => true,
        ]);

        return redirect()->route('admin.cas')
            ->with('success', 'Tax expert added. They can sign in on the main site, and you can assign them on All Orders → Need expert.');
    }

    public function editCa(User $ca)
    {
        if ($ca->role !== 'ca') {
            abort(404);
        }

        $profile = UserProfile::where('user_id', $ca->id)->first();
        $caData = array_merge($ca->toArray(), $profile ? $profile->toArray() : []);

        return view('admin.ca-form', ['ca' => (object) $caData]);
    }

    public function activateCa(User $ca)
    {
        if ($ca->role !== 'ca') {
            abort(404);
        }

        $ca->update(['status' => 'active']);
        UserProfile::where('user_id', $ca->id)->update(['is_available' => true]);

        return back()->with('success', $ca->name.' is active and can be assigned on All Orders.');
    }

    public function updateCa(Request $request, User $ca)
    {
        if ($ca->role !== 'ca') {
            abort(404);
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'status' => 'nullable|in:active,pending,inactive,suspended',
            'password' => 'nullable|string|min:6',
            'membership_no' => 'nullable|string',
            'specialization' => 'nullable|string',
            'experience_years' => 'nullable|integer',
            'max_clients' => 'nullable|integer',
            'bio' => 'nullable|string',
            'is_available' => 'nullable',
        ]);

        $ca->update([
            'name' => $data['name'],
            'phone' => $data['phone'] ?? null,
            'status' => $data['status'] ?? 'active',
        ]);

        if (! empty($data['password'])) {
            $ca->update(['password' => $data['password']]);
        }

        UserProfile::updateOrCreate(
            ['user_id' => $ca->id],
            [
                'membership_no' => $data['membership_no'] ?? null,
                'specialization' => $data['specialization'] ?? null,
                'experience_years' => $data['experience_years'] ?? 0,
                'max_clients' => $data['max_clients'] ?? 50,
                'bio' => $data['bio'] ?? null,
                'is_available' => $request->input('is_available') === '1',
            ]
        );

        return redirect()->route('admin.cas')->with('success', 'Tax expert updated.');
    }

    public function orders(Request $request)
    {
        $query = ItrFiling::with(['user', 'ca', 'plan']);

        $status = (string) $request->input('status', '');
        if ($status === 'complete') {
            // Complete ITR group: filed + completed (ACK / done).
            $query->whereIn('status', $this->completedStatuses());
        } elseif ($status === 'pending') {
            $query->whereIn('status', $this->pendingStatuses());
        } elseif ($status !== '') {
            $query->where('status', $status);
        }

        $q = trim((string) $request->input('q', ''));
        if ($q !== '') {
            $panLike = '%'.strtoupper($q).'%';
            $query->where(function ($b) use ($q, $panLike) {
                if (ctype_digit($q)) {
                    $b->orWhere('id', (int) $q);
                }
                $b->orWhere('pan', 'like', $panLike)
                    ->orWhere('acknowledgement_no', 'like', '%'.$q.'%')
                    ->orWhereHas('user', function ($u) use ($q, $panLike) {
                        $u->where('name', 'like', "%{$q}%")
                            ->orWhere('email', 'like', "%{$q}%")
                            ->orWhere('phone', 'like', "%{$q}%")
                            ->orWhereHas('profile', fn ($p) => $p->where('pan', 'like', $panLike));
                    });
            });
        }

        $orders = $query->orderByDesc('created_at')->paginate(10)->withQueryString();
        $cas = User::withRole('ca')->where('status', 'active')->get(['id', 'name']);
        $needsExpert = ItrFiling::where('status', 'paid')->whereNull('ca_id')->exists();

        return view('admin.orders', [
            'orders' => $orders,
            'cas' => $cas,
            'filter' => $request->status,
            'needsExpert' => $needsExpert,
            'q' => $q,
        ]);
    }

    public function assignCa(Request $request, ItrFiling $filing)
    {
        $request->validate(['ca_id' => 'required|exists:users,id']);

        if (! in_array($filing->status, ['paid', 'assigned'], true)) {
            return back()->with('error', 'Only paid filings can be assigned to a tax expert.');
        }

        $ca = User::withRole('ca')->where('id', (int) $request->ca_id)->where('status', 'active')->first();
        if (! $ca) {
            return back()->with('error', 'Select an active tax expert account.');
        }

        ExpertAssigner::assign($filing, $ca, Auth::id(), 'Assigned from admin orders');

        return back()->with('success', 'Tax expert assigned successfully.');
    }

    public function payments()
    {
        $payments = Payment::with('user')->orderByDesc('created_at')->paginate(10)->withQueryString();

        return view('admin.payments', compact('payments'));
    }

    public function settings()
    {
        $settings = Setting::pluck('setting_value', 'setting_key')->toArray();
        $plans = Plan::orderBy('sort_order')->get();
        $admin = Auth::user();

        return view('admin.settings', compact('settings', 'plans', 'admin'));
    }

    public function saveSettings(Request $request)
    {
        $keys = ['site_name', 'support_email', 'support_phone', 'razorpay_key', 'razorpay_secret', 'company_address'];
        foreach ($keys as $key) {
            Setting::setValue($key, $request->input($key));
        }

        return back()->with('success', 'Settings saved.');
    }

    public function updateAccount(Request $request)
    {
        /** @var User $admin */
        $admin = Auth::user();

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'max:255',
                'regex:/^[A-Za-z0-9._%+\-@]+$/',
                Rule::unique('users', 'email')->ignore($admin->id),
            ],
            'phone' => 'nullable|string|max:20',
            'current_password' => 'required|string',
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        if (! Hash::check($data['current_password'], $admin->password)) {
            return back()->withInput($request->except('current_password', 'password', 'password_confirmation'))
                ->with('error', 'Current password is incorrect.');
        }

        $payload = [
            'name' => trim($data['name']),
            'email' => strtolower(trim($data['email'])),
            'phone' => $data['phone'] ?? null,
        ];

        if (! empty($data['password'])) {
            $payload['password'] = $data['password']; // hashed via User cast → saved in users.password
        }

        $admin->forceFill($payload)->save();
        Auth::setUser($admin->fresh());

        $msg = 'Admin login saved in database (users table).';
        if (! empty($data['password'])) {
            $msg .= ' Password updated.';
        }
        $msg .= ' Sign in next time with: '.$admin->email;

        return back()->with('success', $msg);
    }

    public function updatePlan(Request $request, Plan $plan)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'is_active' => 'nullable',
        ]);

        $plan->update([
            'name' => $data['name'],
            'price' => $data['price'],
            'description' => $data['description'] ?? null,
            'is_active' => $request->input('is_active') === '1',
        ]);

        return back()->with('success', 'Plan updated.');
    }
}
