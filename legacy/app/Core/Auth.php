<?php

namespace App\Core;

class Auth
{
    public static function attempt(string $email, string $password): bool
    {
        $user = Database::fetch('SELECT * FROM users WHERE email = ? AND status = ?', [$email, 'active']);
        if (!$user || !password_verify($password, $user['password'])) {
            return false;
        }
        unset($user['password']);
        Session::set('user', $user);
        return true;
    }

    public static function login(array $user): void
    {
        unset($user['password']);
        Session::set('user', $user);
    }

    public static function logout(): void
    {
        Session::forget('user');
    }

    public static function check(): bool
    {
        return Session::get('user') !== null;
    }

    public static function user(): ?array
    {
        return Session::get('user');
    }

    public static function id(): ?int
    {
        $user = self::user();
        return $user ? (int) $user['id'] : null;
    }

    public static function role(): ?string
    {
        return self::user()['role'] ?? null;
    }

    public static function is(string $role): bool
    {
        return self::role() === $role;
    }

    public static function refresh(): void
    {
        if (!self::check()) {
            return;
        }
        $user = Database::fetch('SELECT * FROM users WHERE id = ?', [self::id()]);
        if ($user) {
            self::login($user);
        }
    }
}
