<?php

namespace App\Middleware;

use App\Core\Auth;
use App\Core\Session;

class UserMiddleware
{
    public function handle(): void
    {
        if (!Auth::check() || Auth::role() !== 'user') {
            Session::flash('error', 'Access denied.');
            header('Location: /login');
            exit;
        }
    }
}
