<?php

namespace App\Http\Middleware;

use App\Support\Portal;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Admin routes only on the configured admin host/IP when separation is on. */
class EnsureAdminHost
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Portal::separationEnabled()) {
            return $next($request);
        }

        if (! Portal::isAdminHost($request)) {
            return redirect()->away(Portal::adminPath($request->getRequestUri()))
                ->with('info', 'Open the admin portal on its dedicated address.');
        }

        return $next($request);
    }
}
