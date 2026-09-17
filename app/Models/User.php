<?php

/**
 * File: app/Models/User.php
 * Responsibility: Application user (internal staff or customer-portal user).
 * What it does:
 * - Authenticates via spatie roles/permissions (Filament Shield) and via
 *   one-time passwords (otpz) for the customer portal.
 * - Links to the companies a user may handle through the company_user pivot.
 * - Restricts Filament impersonation: only admin/super_admin may act, and a
 *   super_admin target needs a super_admin actor (no upward escalation).
 * How to use: `$user->companies`, `$user->hasRole('admin')`, `$user->isInternal()`.
 * How to extend: Add profile fields; keep role logic in spatie roles, not columns.
 */

namespace App\Models;

use App\Models\Concerns\HasNotes;
use BenBjurstrom\Otpz\Models\Concerns\HasOtps;
use BenBjurstrom\Otpz\Models\Concerns\Otpable;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
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
    use HasFactory, HasNotes, HasOtps, HasRoles, Notifiable;

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
     * Company ids this user is assigned to — the row-level scope for
     * operators (admin panel) and customers (portal). Privileged staff are
     * unscoped and never need this.
     *
     * @return list<int>
     */
    public function companyIds(): array
    {
        return array_map('intval', $this->companies()->pluck('companies.id')->all());
    }

    /**
     * Constrain a query to the companies this user is assigned to;
     * privileged staff are left unscoped. Used by admin pickers and table
     * filters so operators are never offered records outside their scope.
     *
     * @param  Builder<*>  $query
     * @return Builder<*>
     */
    public static function scopeToAssignedCompanies(Builder $query, string $column = 'id'): Builder
    {
        $user = auth()->user();

        if (! $user instanceof self || $user->hasAnyRole(Role::PRIVILEGED)) {
            return $query;
        }

        return $query->whereIn($column, $user->companyIds());
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
     * Admins see every shipment in the portal; everyone else is scoped to
     * their assigned companies. Operators stay scoped (no portal login).
     */
    public function canViewAllShipments(): bool
    {
        return $this->hasAnyRole(Role::PRIVILEGED);
    }

    /**
     * Only admins may impersonate (stechstudio/filament-impersonate).
     */
    public function canImpersonate(): bool
    {
        return $this->hasAnyRole(Role::PRIVILEGED);
    }

    /**
     * Super admins may only be impersonated by another super admin, so an
     * admin can never escalate by impersonating upwards.
     */
    public function canBeImpersonated(): bool
    {
        if ($this->hasRole(Role::SUPER_ADMIN)) {
            return (bool) auth()->user()?->hasRole(Role::SUPER_ADMIN);
        }

        return true;
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
