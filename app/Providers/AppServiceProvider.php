<?php

namespace App\Providers;

use App\Support\Portal;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        require_once app_path('Helpers/helpers.php');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        view()->share('app', config('itr'));
        Paginator::defaultView('vendor.pagination.itr');
        Paginator::defaultSimpleView('vendor.pagination.itr');

        if (! app()->runningInConsole() && Portal::separationEnabled()) {
            if (Portal::isAdminHost()) {
                URL::forceRootUrl(Portal::adminBaseUrl());
                // Separate cookie so admin :8001 and public :8000 sessions do not clash on the same IP.
                config([
                    'session.cookie' => config('session.cookie').'_admin',
                ]);
            } else {
                URL::forceRootUrl(Portal::publicBaseUrl());
            }
        }
    }
}
