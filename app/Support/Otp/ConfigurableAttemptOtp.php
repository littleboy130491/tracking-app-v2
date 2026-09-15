<?php

/**
 * File: app/Support/Otp/ConfigurableAttemptOtp.php
 * Responsibility: Verifies a one-time password with a configurable attempt cap.
 * What it does:
 * - Extends otpz's AttemptOtp, which hardcodes a limit of 3 wrong codes. This
 *   subclass reads the limit from config so it can be tuned per environment
 *   (OTPZ_MAX_ATTEMPTS, default 8).
 * - Everything else (signed request, session lock, expiry, one-time use, code
 *   comparison) is still enforced by the parent class.
 * How to use: app(ConfigurableAttemptOtp::class)->handle($otpId, $code, $sessionId).
 * How to extend: override other protected validate*() methods to change a rule.
 */

namespace App\Support\Otp;

use BenBjurstrom\Otpz\Actions\AttemptOtp;
use BenBjurstrom\Otpz\Enums\OtpStatus;
use BenBjurstrom\Otpz\Exceptions\OtpAttemptException;
use BenBjurstrom\Otpz\Models\Otp;

class ConfigurableAttemptOtp extends AttemptOtp
{
    /**
     * Invalidate the code once too many wrong codes have been submitted.
     *
     * The check runs before the submitted code is compared, so with a limit of
     * 8 a user may get the code wrong 8 times; the 9th attempt is refused.
     */
    protected function validateAttempts(Otp $otp): void
    {
        $maxAttempts = (int) config('otpz.max_attempts', 8);

        if ($otp->attempts >= $maxAttempts) {
            $otp->update(['status' => OtpStatus::ATTEMPTED]);

            throw new OtpAttemptException(OtpStatus::ATTEMPTED->errorMessage());
        }
    }
}
