<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Models\UserProfile;
use App\Support\Portal;
use App\Support\Validation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin(Request $request)
    {
        if (Auth::check()) {
            return redirect()->away($this->dashboardUrl(Auth::user()));
        }

        return view('auth.login', [
            'mode' => $request->query('mode'),
            'planId' => $request->query('plan_id'),
            'isAdminPortal' => Portal::isAdminHost($request),
            'adminPortalUrl' => Portal::adminBaseUrl(),
            'publicPortalUrl' => Portal::publicBaseUrl(),
            'portalSeparated' => Portal::separationEnabled(),
        ]);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|string|max:255',
            'password' => 'required|string',
        ]);

        $login = strtolower(trim($credentials['email']));
        $user = User::query()->where('email', $login)->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return back()
                ->withInput($request->only('email', 'mode', 'plan_id', 'account_type'))
                ->with('error', 'Invalid login ID or password.');
        }

        if ($user->status !== 'active') {
            $msg = $user->status === 'pending'
                ? 'Your account is pending admin approval.'
                : 'Your account is not active. Contact support.';

            return back()->withInput($request->only('email'))->with('error', $msg);
        }

        // Separate portals: admin only on admin host; customers/experts only on main site.
        if (Portal::separationEnabled()) {
            if (Portal::isAdminHost($request) && ! $user->isAdmin()) {
                return back()->withInput($request->only('email'))
                    ->with('error', 'This admin portal is for administrators only. Customers and tax experts sign in here: '.Portal::publicBaseUrl().'/login');
            }
            if (! Portal::isAdminHost($request) && $user->isAdmin()) {
                Auth::logout();

                return redirect()->away(Portal::adminPath('/login'))
                    ->with('info', 'Admin login is only on the admin portal: '.Portal::adminPath('/login'));
            }
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return $this->redirectAfterAuth($request, $user, 'Welcome back, '.$user->name.'!');
    }

    public function showRegister(Request $request)
    {
        if (Portal::separationEnabled() && Portal::isAdminHost($request)) {
            return redirect()->route('login')
                ->with('info', 'Registration is on the main site only.');
        }

        if (Auth::check()) {
            return redirect()->away($this->dashboardUrl(Auth::user()));
        }

        if ($request->query('type') === 'expert') {
            return redirect()->route('register')
                ->with('info', 'Tax expert accounts are created by admin from the admin portal.');
        }

        return view('auth.register', [
            'mode' => $request->query('mode'),
            'planId' => $request->query('plan_id'),
        ]);
    }

    public function register(Request $request)
    {
        if (Portal::separationEnabled() && Portal::isAdminHost($request)) {
            return redirect()->away(Portal::publicPath('/register'))
                ->with('info', 'Create accounts on the main site.');
        }

        // Public signup is customers only. Tax experts are added by admin.
        if ($request->input('account_type') === 'expert') {
            return redirect()->route('register')
                ->with('info', 'Tax expert accounts are created by admin from the admin portal.');
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'phone' => Validation::phone(),
            'pan' => Validation::panOptional(),
            'password' => 'required|string|min:6|confirmed',
            'account_type' => 'nullable|in:user',
            'mode' => 'nullable|in:self,assisted',
            'plan_id' => 'nullable|integer|exists:plans,id',
        ]);

        $user = User::create([
            'name' => trim($data['name']),
            'email' => strtolower(trim($data['email'])),
            'phone' => $data['phone'],
            'password' => $data['password'],
            'role_id' => Role::idFor('user'),
            'status' => 'active',
            'email_verified_at' => null,
        ]);

        UserProfile::create([
            'user_id' => $user->id,
            'pan' => isset($data['pan']) ? strtoupper(trim($data['pan'])) : null,
            'experience_years' => 0,
            'max_clients' => 50,
            'is_available' => true,
        ]);

        $user->load(['roleRelation', 'profile']);
        Auth::login($user);
        $request->session()->regenerate();

        $mode = $data['mode'] ?? null;
        $planId = $data['plan_id'] ?? null;
        if (in_array($mode, ['self', 'assisted'], true)) {
            $request->session()->put('pending_filing_mode', $mode);
            $request->session()->put('pending_plan_id', $planId);
        }

        return redirect()
            ->route('user.complete-profile')
            ->with('success', 'Account created. Complete your profile to continue.');
    }

    public function logout(Request $request)
    {
        $wasAdminHost = Portal::isAdminHost($request);

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if (Portal::separationEnabled() && $wasAdminHost) {
            return redirect()->route('login')->with('success', 'Logged out successfully.');
        }

        return redirect()->route('home')->with('success', 'Logged out successfully.');
    }

    private function redirectAfterAuth(Request $request, User $user, string $message)
    {
        if ($user->isAdmin()) {
            $target = $this->safeIntended($user);
            if (Portal::separationEnabled() && ! Portal::isAdminHost($request)) {
                return redirect()->away(Portal::adminPath('/admin'))->with('success', $message);
            }

            return redirect()->to($target)->with('success', $message);
        }

        if ($user->isCa()) {
            return redirect()->to($this->safeIntended($user))->with('success', $message);
        }

        // Customer
        $mode = $request->input('mode', $request->query('mode'));
        $planId = $request->input('plan_id', $request->query('plan_id'));

        if (in_array($mode, ['self', 'assisted'], true)) {
            $request->session()->put('pending_filing_mode', $mode);
            $request->session()->put('pending_plan_id', $planId);
        }

        if (! profileIsComplete($user)) {
            return redirect()->route('user.complete-profile')->with('success', $message);
        }

        if (in_array($mode, ['self', 'assisted'], true)) {
            $request->session()->forget(['pending_filing_mode', 'pending_plan_id']);

            return redirect()->route('user.choose-service', array_filter([
                'mode' => $mode,
                'plan_id' => $planId ?: null,
            ]))->with('success', $message);
        }

        // Normal customer login → dashboard
        return redirect()->to($this->safeIntended($user))->with('success', $message);
    }

    private function dashboardUrl(User $user): string
    {
        if ($user->isAdmin()) {
            return Portal::separationEnabled()
                ? Portal::adminPath('/admin')
                : route('admin.dashboard');
        }
        if ($user->isCa()) {
            return route('ca.dashboard');
        }
        if (! profileIsComplete($user)) {
            return route('user.complete-profile');
        }

        return route('user.dashboard');
    }

    /** Only follow url.intended when it belongs to this role's area. */
    private function safeIntended(User $user): string
    {
        $default = $this->dashboardUrl($user);
        $intended = $this->pullIntendedUrl();

        if (! $intended) {
            return $default;
        }

        $path = parse_url($intended, PHP_URL_PATH) ?: '';
        $path = '/'.ltrim($path, '/');

        if ($user->isAdmin()) {
            return str_starts_with($path, '/admin') ? $intended : $default;
        }
        if ($user->isCa()) {
            return str_starts_with($path, '/ca') ? $intended : $default;
        }

        // Customer: allow user panel routes, never admin/ca
        if (str_starts_with($path, '/admin') || str_starts_with($path, '/ca')) {
            return $default;
        }
        if (str_starts_with($path, '/dashboard')
            || str_starts_with($path, '/itr')
            || str_starts_with($path, '/documents')
            || str_starts_with($path, '/summary')
            || str_starts_with($path, '/review')
            || str_starts_with($path, '/payment')
            || str_starts_with($path, '/track')
            || str_starts_with($path, '/acknowledgement')
            || str_starts_with($path, '/profile')) {
            return $intended;
        }

        return $default;
    }

    private function pullIntendedUrl(): ?string
    {
        $intended = session()->pull('url.intended');
        if (! is_string($intended) || $intended === '') {
            return null;
        }

        return $intended;
    }
}
