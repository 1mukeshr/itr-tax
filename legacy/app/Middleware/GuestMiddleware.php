<?php

namespace App\Middleware;

use App\Core\Auth;
use App\Core\Session;

class GuestMiddleware
{
    public function handle(): void
    {
        if (Auth::check()) {
            $role = Auth::role();
            $path = match ($role) {
                'admin' => '/admin',
                'ca' => '/ca',
                default => '/dashboard',
            };
            header('Location: ' . $path);
            exit;
        }
    }
}
