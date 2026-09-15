<?php

/**
 * File: app/Http/Controllers/Customer/LoginController.php
 * Responsibility: Passwordless (one-time password) login for the customer portal.
 * What it does:
 * - Shows the email form, sends a one-time password to a registered customer
 *   account, then shows the verify screen for the code.
 * - Verification uses App\Support\Otp\ConfigurableAttemptOtp, which enforces the
 *   signed URL, session lock, expiry, one-time use and a configurable attempt cap.
 * - Failures render as friendly messages on the form instead of a raw 403.
 * - A cache-backed rate limiter (OTPZ_MAX_ATTEMPTS, default 8) limits how often
 *   codes may be guessed from one IP, across codes. It is cleared on success and
 *   deliberately NOT cleared when a new code is requested, so requesting more
 *   codes cannot be used to reset it.
 * How to use: routes customer.login / customer.login.send / customer.verify.
 * How to extend: add SMS delivery by customising otpz's mailable/channel.
 */

namespace App\Http\Controllers\Customer;

use App\Models\User;
use App\Support\Otp\ConfigurableAttemptOtp;
use BenBjurstrom\Otpz\Actions\SendOtp;
use BenBjurstrom\Otpz\Exceptions\OtpAttemptException;
use BenBjurstrom\Otpz\Exceptions\OtpThrottleException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;

class LoginController
{
    public function showLogin(): View
    {
        return view('customer.login');
    }

    /**
     * Send a one-time password and continue to the signed verify screen.
     */
    public function send(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
        ]);

        try {
            $otp = app(SendOtp::class)->handle($data['email']);
        } catch (OtpThrottleException) {
            return back()
                ->withErrors(['email' => 'Too many codes requested. Please wait a moment and try again.'])
                ->onlyInput('email');
        }

        // The link stays valid a little longer than the code itself, so a stale
        // code reports "expired" instead of an invalid-signature error.
        return redirect(URL::temporarySignedRoute('customer.verify', now()->addMinutes(
            (int) config('otpz.expiration', 5) + 10,
        ), [
            'otp' => $otp->getKey(),
            'sessionId' => $request->session()->getId(),
        ]));
    }

    /**
     * The screen where the customer types the code. The signature is checked here
     * (rather than by the `signed` middleware) so an expired link shows a friendly
     * message on the login form.
     */
    public function showVerify(Request $request, string $otp): View|RedirectResponse
    {
        if (! $request->hasValidSignature()) {
            return redirect()
                ->route('customer.login')
                ->withErrors(['email' => 'That sign-in link is no longer valid. Please request a new code.']);
        }

        return view('customer.verify', [
            'otp' => $otp,
            'sessionId' => (string) $request->query('sessionId', ''),
            'devCode' => config('otpz.expose_in_dev') ? $request->session()->get('otpz_dev_code') : null,
        ]);
    }

    /**
     * Verify the code and start the session.
     */
    public function verify(Request $request, string $otp): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string'],
            'sessionId' => ['required', 'string'],
        ]);

        $throttleKey = $this->throttleKey($request);
        $maxAttempts = (int) config('otpz.max_attempts', 8);

        if (RateLimiter::tooManyAttempts($throttleKey, $maxAttempts)) {
            return back()->withErrors([
                'code' => 'Too many incorrect codes. Please try again in '
                    .RateLimiter::availableIn($throttleKey).' seconds.',
            ]);
        }

        try {
            $record = app(ConfigurableAttemptOtp::class)->handle($otp, $data['code'], $data['sessionId']);
        } catch (OtpAttemptException $exception) {
            RateLimiter::hit($throttleKey, $this->decaySeconds());

            return back()->withErrors(['code' => $exception->getMessage()]);
        }

        RateLimiter::clear($throttleKey);

        /** @var User|null $user */
        $user = $record->user;

        if (! $user || ! $user->is_active) {
            return redirect()
                ->route('customer.login')
                ->withErrors(['email' => 'This account is no longer active.']);
        }

        Auth::login($user, (bool) $record->remember);
        $request->session()->regenerate();
        $request->session()->forget('otpz_dev_code');

        return redirect()->intended(route('customer.dashboard'));
    }

    /**
     * One bucket per IP: guessing codes for many different OTP ids still counts.
     */
    private function throttleKey(Request $request): string
    {
        return 'otp-verify|'.$request->ip();
    }

    private function decaySeconds(): int
    {
        return max(1, (int) config('otpz.attempt_decay_minutes', 10)) * 60;
    }
}
