<?php

namespace App\Http\Middleware;

use App\Support\Portal;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login')->with('error', 'Please sign in to continue.');
        }

        if ($user->status !== 'active') {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('home')->with('error', 'Your account is not active. Contact support.');
        }

        if (! in_array($user->role, $roles, true)) {
            $home = match (true) {
                $user->isAdmin() => Portal::separationEnabled()
                    ? Portal::adminPath('/admin')
                    : route('admin.dashboard'),
                $user->isCa() => route('ca.dashboard'),
                default => profileIsComplete($user) ? route('user.dashboard') : route('user.complete-profile'),
            };

            if ($user->isAdmin() && Portal::separationEnabled() && ! Portal::isAdminHost($request)) {
                return redirect()->away($home)->with('error', 'Open the admin portal to continue.');
            }

            return redirect()->to($home)->with('error', 'You do not have access to that page.');
        }

        return $next($request);
    }
}
