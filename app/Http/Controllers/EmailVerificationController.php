<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;

class EmailVerificationController extends Controller
{
    public function send(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return back()->with('info', 'Your email is already verified.');
        }

        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        // Soft verify: no hard gate. Demo/log mailers surface the link in-session.
        if (in_array(config('mail.default'), ['log', 'array'], true)) {
            return back()
                ->with('success', 'Verification link ready (demo mailer).')
                ->with('demo_verify_url', $url);
        }

        // Production: wire SMTP to email $url.
        return back()->with('success', 'Verification link sent. Check your email.');
    }

    public function verify(Request $request, int $id, string $hash)
    {
        if (! $request->hasValidSignature()) {
            return redirect()->route('login')->with('error', 'Invalid or expired verification link.');
        }

        $user = User::findOrFail($id);

        if (! hash_equals($hash, sha1($user->email))) {
            return redirect()->route('login')->with('error', 'Invalid verification link.');
        }

        if (! $user->hasVerifiedEmail()) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        if (Auth::check() && Auth::id() === $user->id) {
            return redirect()->route('user.dashboard')->with('success', 'Email verified. Thank you!');
        }

        return redirect()->route('login')->with('success', 'Email verified. You can sign in.');
    }
}
