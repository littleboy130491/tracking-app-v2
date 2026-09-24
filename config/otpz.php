<?php

use App\Mail\DevOtpMail;
use App\Models\User;
use App\Support\Otp\RegisteredUserResolver;

return [
    /*
    |--------------------------------------------------------------------------
    | Expiration and Throttling
    |--------------------------------------------------------------------------
    |
    | These settings control the security aspects of the generated codes,
    | including their expiration time and the throttling mechanism to prevent
    | abuse.
    |
    */

    'expiration' => (int) env('OTPZ_EXPIRATION', 5), // Minutes

    /*
    |--------------------------------------------------------------------------
    | Attempts and Rate Limiting
    |--------------------------------------------------------------------------
    |
    | `max_attempts` is how many incorrect codes may be submitted before the code
    | is invalidated; the same number caps guessing per IP within the decay
    | window. `attempt_decay_minutes` is how long that per-IP lockout lasts.
    |
    */

    'max_attempts' => (int) env('OTPZ_MAX_ATTEMPTS', 8),

    'attempt_decay_minutes' => (int) env('OTPZ_ATTEMPT_DECAY_MINUTES', 10),

    /*
    |--------------------------------------------------------------------------
    | Development Convenience
    |--------------------------------------------------------------------------
    |
    | When true (and not in production) the plaintext one-time password is also
    | stored in the session and shown on the verify screen, so local development
    | does not depend on reading the mail log. Never enable this in production.
    |
    */

    'expose_in_dev' => env('OTPZ_EXPOSE_IN_DEV', false),

    /*
    | OTPZ_DISABLE_LIMITS=true turns the code-request throttle off entirely.
    | Local testing only: the flag is ignored in production so the throttle
    | can never be disabled there by accident.
    */

    'limits' => filter_var(env('OTPZ_DISABLE_LIMITS', false), FILTER_VALIDATE_BOOL) && env('APP_ENV') !== 'production'
        ? []
        : [
            ['limit' => 1, 'minutes' => 1],
            ['limit' => 3, 'minutes' => 5],
            ['limit' => 5, 'minutes' => 30],
        ],

    /*
    |--------------------------------------------------------------------------
    | Model Configuration
    |--------------------------------------------------------------------------
    |
    | This setting determines the model used by Otpz to store and retrieve
    | one-time passwords. By default, it uses the 'App\Models\User' model.
    |
    */

    'models' => [
        'authenticatable' => User::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Mailable Configuration
    |--------------------------------------------------------------------------
    |
    | This setting determines the Mailable class used by Otpz to send emails.
    | Change this to your own Mailable class if you want to customize the email
    | sending behavior.
    |
    */

    'mailable' => DevOtpMail::class,

    /*
    |--------------------------------------------------------------------------
    | Template Configuration
    |--------------------------------------------------------------------------
    |
    | This setting determines the email template used by Otpz to send emails.
    | Switch to 'otpz::mail.notification' if you prefer to use the default
    | Laravel notification template.
    |
    */

    'template' => 'otpz::mail.otpz',
    // 'template' => 'otpz::mail.notification',

    /*
    |--------------------------------------------------------------------------
    | User Resolver
    |--------------------------------------------------------------------------
    |
    | Defines the class responsible for finding or creating users by email address.
    | The default implementation will create a new user when an email doesn't exist.
    | Replace with your own implementation for custom user resolution logic.
    |
    */

    'user_resolver' => RegisteredUserResolver::class,
];
