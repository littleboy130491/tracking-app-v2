<?php

/**
 * File: app/Mail/DevOtpMail.php
 * Responsibility: Sends the one-time password and, in development, exposes it.
 * What it does:
 * - Extends the package mailable so the email content is unchanged.
 * - When OTPZ_EXPOSE_IN_DEV is true the plaintext code is also stored in the
 *   session, so a developer can read it on the verify screen instead of
 *   checking the mail log. Never enable this in production.
 * How to use: referenced by config/otpz.php `mailable`.
 * How to extend: swap the template in config/otpz.php for custom branding.
 */

namespace App\Mail;

use BenBjurstrom\Otpz\Mail\OtpzMail;
use BenBjurstrom\Otpz\Models\Otp;

class DevOtpMail extends OtpzMail
{
    public function __construct(Otp $otp, string $code)
    {
        parent::__construct($otp, $code);

        if (config('otpz.expose_in_dev') && ! app()->isProduction()) {
            session()->put('otpz_dev_code', $code);
        }
    }
}
