<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Session;

class AuthController extends Controller
{
    public function showLogin(): void
    {
        $this->view('auth/login', ['title' => 'Login'], 'layouts/auth');
    }

    public function login(): void
    {
        $this->validateCsrf();
        $email = strtolower($this->input('email'));
        $password = $_POST['password'] ?? '';

        if (!Auth::attempt($email, $password)) {
            Session::flash('error', 'Invalid email or password.');
            $this->redirect('/login');
        }

        Session::flash('success', 'Welcome back, ' . Auth::user()['name'] . '!');
        $path = match (Auth::role()) {
            'admin' => '/admin',
            'ca' => '/ca',
            default => '/dashboard',
        };
        $this->redirect($path);
    }

    public function showRegister(): void
    {
        $this->view('auth/register', ['title' => 'Create Account'], 'layouts/auth');
    }

    public function register(): void
    {
        $this->validateCsrf();

        $name = $this->input('name');
        $email = strtolower($this->input('email'));
        $phone = $this->input('phone');
        $pan = strtoupper($this->input('pan'));
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['password_confirmation'] ?? '';

        if (!$name || !$email || !$password) {
            Session::flash('error', 'Please fill all required fields.');
            $this->redirect('/register');
        }

        if ($password !== $confirm) {
            Session::flash('error', 'Passwords do not match.');
            $this->redirect('/register');
        }

        if (strlen($password) < 6) {
            Session::flash('error', 'Password must be at least 6 characters.');
            $this->redirect('/register');
        }

        $exists = Database::fetch('SELECT id FROM users WHERE email = ?', [$email]);
        if ($exists) {
            Session::flash('error', 'Email already registered. Please login.');
            $this->redirect('/login');
        }

        $id = Database::insert('users', [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'pan' => $pan ?: null,
            'password' => password_hash($password, PASSWORD_BCRYPT),
            'role' => 'user',
            'status' => 'active',
        ]);

        $user = Database::fetch('SELECT * FROM users WHERE id = ?', [$id]);
        Auth::login($user);
        Session::flash('success', 'Account created successfully. Start your ITR filing!');
        $this->redirect('/dashboard');
    }

    public function logout(): void
    {
        Auth::logout();
        Session::flash('success', 'Logged out successfully.');
        $this->redirect('/login');
    }
}
