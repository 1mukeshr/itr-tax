<?php

namespace App\Middleware;

use App\Core\Auth;
use App\Core\Session;

class AdminMiddleware
{
    public function handle(): void
    {
        if (!Auth::check() || Auth::role() !== 'admin') {
            Session::flash('error', 'Admin access only.');
            header('Location: /login');
            exit;
        }
    }
}
