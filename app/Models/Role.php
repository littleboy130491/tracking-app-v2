<?php

/**
 * File: app/Models/Role.php
 * Responsibility: spatie role extended with the internal/customer flag.
 * What it does:
 * - Adds the `is_internal` column defined by migration_plan.md §3 to the role
 *   model used by Filament Shield.
 * - Declares the canonical role names used across the application.
 * - Marks the privileged roles (super_admin, admin) that only a super admin may
 *   hand out; see App\Support\Authorization\AssignableRoles.
 * How to use: `$user->assignRole(Role::ADMIN)`; seed with RoleSeeder.
 * How to extend: Add constants and seed entries for new staff roles.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Spatie\Permission\Models\Role as SpatieRole;

#[Fillable(['name', 'guard_name', 'is_internal'])]
class Role extends SpatieRole
{
    /**
     * Bypasses every gate (Filament Shield's super admin) and is the only role
     * that may create or edit admins.
     */
    public const SUPER_ADMIN = 'super_admin';

    /** Manages everything the seeded permissions allow, except other admins. */
    public const ADMIN = 'admin';

    /** Granular, per-resource permissions; operates shipment records. */
    public const OPERATOR = 'operator';

    /** Customer portal only; no admin panel access. */
    public const CUSTOMER = 'customer';

    /**
     * Roles only a super admin may assign to (or take away from) a user.
     *
     * @var array<int, string>
     */
    public const PRIVILEGED = [self::SUPER_ADMIN, self::ADMIN];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_internal' => 'boolean',
        ];
    }

    public function isPrivileged(): bool
    {
        return in_array($this->name, self::PRIVILEGED, true);
    }
}
