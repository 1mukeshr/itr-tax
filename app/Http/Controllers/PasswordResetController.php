<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    public function showForgot()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink($request->only('email'));

        $payload = ['status' => __($status)];

        // Local/demo: surface reset URL when mailer is log/array.
        if ($status === Password::RESET_LINK_SENT && in_array(config('mail.default'), ['log', 'array'], true)) {
            $user = User::where('email', strtolower($request->email))->first();
            if ($user) {
                $token = Password::createToken($user);
                $payload['demo_reset_url'] = url(route('password.reset', [
                    'token' => $token,
                    'email' => $user->email,
                ], false));
            }
        }

        return $status === Password::RESET_LINK_SENT
            ? back()->with('success', $payload['status'])
                ->with('demo_reset_url', $payload['demo_reset_url'] ?? null)
            : back()->withInput($request->only('email'))->with('error', __($status));
    }

    public function showReset(Request $request, string $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email', ''),
        ]);
    }

    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => $password,
                    'remember_token' => Str::random(60),
                    'email_verified_at' => $user->email_verified_at ?? now(),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('success', __($status))
            : back()->withInput($request->only('email'))->with('error', __($status));
    }
}
