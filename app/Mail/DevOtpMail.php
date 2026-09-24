<?php

/**
 * File: app/Mail/DevOtpMail.php
 * Responsibility: Sends the one-time password and, in development, exposes it.
 * What it does:
 * - Extends the package mailable so the email content is unchanged.
 * - When OTPZ_EXPOSE_IN_DEV is true (and the app runs on local or under
 *   phpunit) the plaintext code is also stored in the session, so a
 *   developer can read it on the verify screen even when mail delivery
 *   itself fails. Any other environment ignores the flag.
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

        if (config('otpz.expose_in_dev') && in_array(app()->environment(), ['local', 'testing'], true)) {
            session()->put('otpz_dev_code', $code);
        }
    }
}
