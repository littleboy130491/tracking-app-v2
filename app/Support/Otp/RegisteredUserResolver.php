<?php

/**
 * File: app/Support/Otp/RegisteredUserResolver.php
 * Responsibility: Resolves the user for an OTP login request.
 * What it does:
 * - Replaces otpz's default resolver, which silently creates a user when the
 *   email is unknown. spec.md has no registration: only an existing, active
 *   account with the customer role — or an admin/super_admin, who see every
 *   shipment in the portal — may request a one-time password. Operators stay
 *   blocked: they work in the admin panel, not the portal.
 * How to use: referenced by config/otpz.php `user_resolver`.
 * How to extend: widen the role check if internal staff should use the portal.
 */

namespace App\Support\Otp;

use App\Models\Role;
use App\Models\User;
use BenBjurstrom\Otpz\Models\Concerns\Otpable;
use BenBjurstrom\Otpz\Support\Config;
use Illuminate\Validation\ValidationException;

class RegisteredUserResolver
{
    public function handle(string $email): Otpable
    {
        $model = Config::getAuthenticatableModel();

        /** @var User|null $user */
        $user = $model::query()->where('email', $email)->first();

        if (! $user || ! $user->is_active || ! ($user->hasRole(Role::CUSTOMER) || $user->canViewAllShipments())) {
            // Same message for "unknown" and "not allowed" so the form cannot be
            // used to enumerate customer email addresses.
            throw ValidationException::withMessages([
                'email' => 'No active customer account matches that email address.',
            ]);
        }

        return $user;
    }
}
