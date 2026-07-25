<?php

use App\Http\Middleware\EnsureAdminHost;
use App\Http\Middleware\EnsurePublicHost;
use App\Http\Middleware\EnsureRole;
use App\Support\Portal;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => EnsureRole::class,
            'admin.host' => EnsureAdminHost::class,
            'public.host' => EnsurePublicHost::class,
        ]);

        $middleware->appendToGroup('web', [
            EnsurePublicHost::class,
        ]);

        $middleware->redirectGuestsTo(function () {
            if (Portal::isAdminHost()) {
                return route('login');
            }

            return route('login');
        });

        $middleware->redirectUsersTo(function () {
            $user = auth()->user();
            if (! $user) {
                return route('home');
            }
            if ($user->isAdmin()) {
                if (Portal::separationEnabled() && ! Portal::isAdminHost()) {
                    return Portal::adminPath('/admin');
                }

                return route('admin.dashboard');
            }
            if ($user->isCa()) {
                return route('ca.dashboard');
            }
            if (! profileIsComplete($user)) {
                return route('user.complete-profile');
            }

            return route('user.dashboard');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
