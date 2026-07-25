<?php

namespace App\Middleware;

use App\Core\Auth;
use App\Core\Session;

class CaMiddleware
{
    public function handle(): void
    {
        if (!Auth::check() || Auth::role() !== 'ca') {
            Session::flash('error', 'CA access only.');
            header('Location: /login');
            exit;
        }
    }
}
