<?php

/**
 * File: app/Models/User.php
 * Responsibility: Application user (internal staff or customer-portal user).
 * What it does:
 * - Authenticates via spatie roles/permissions (Filament Shield) and via
 *   one-time passwords (otpz) for the customer portal.
 * - Links to the companies a user may handle through the company_user pivot.
 * How to use: `$user->companies`, `$user->hasRole('admin')`, `$user->isInternal()`.
 * How to extend: Add profile fields; keep role logic in spatie roles, not columns.
 */

namespace App\Models;

use BenBjurstrom\Otpz\Models\Concerns\HasOtps;
use BenBjurstrom\Otpz\Models\Concerns\Otpable;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'phone', 'password', 'is_active'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser, Otpable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasOtps, HasRoles, Notifiable;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Companies this user may handle (many-to-many, see spec.md).
     *
     * @return BelongsToMany<Company, $this>
     */
    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class)->withTimestamps();
    }

    /**
     * Staff roles (admin, operator) are flagged internal; the portal role is not.
     */
    public function isInternal(): bool
    {
        return $this->roles()->where('is_internal', true)->exists();
    }

    public function isCustomer(): bool
    {
        return $this->hasRole(Role::CUSTOMER);
    }

    /**
     * Only active internal users may reach the admin panel; customer-portal
     * users are kept out entirely (spec.md: regular users see the customer
     * dashboard only).
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_active && $this->isInternal();
    }
}
