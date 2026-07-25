<?php

namespace App\Http\Middleware;

use App\Support\Portal;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Keep customer/expert site off the admin host when separation is on. */
class EnsurePublicHost
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Portal::separationEnabled() || ! Portal::isAdminHost($request)) {
            return $next($request);
        }

        // Allowed on admin host: login/logout + admin panel
        if ($request->is('login', 'logout', 'admin', 'admin/*', 'up')) {
            return $next($request);
        }

        if (auth()->check() && auth()->user()->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('login');
    }
}
