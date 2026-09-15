<?php

/**
 * File: app/Support/Authorization/AssignableRoles.php
 * Responsibility: Single source of truth for who may assign which role.
 * What it does:
 * - Decides which roles a user may hand out: a super admin may assign anything,
 *   everyone else is limited to the unprivileged roles (operator, customer).
 * - Feeds the roles field in the admin user form. Because Filament validates a
 *   submitted value against the options that were on offer, an admin cannot
 *   assign (or strip) a privileged role even with a tampered request — this is
 *   server-side enforcement, not just a hidden option.
 * How to use: `AssignableRoles::optionsFor($actor)` in the user form.
 * How to extend: add a role to Role::PRIVILEGED to restrict it the same way.
 */

namespace App\Support\Authorization;

use App\Models\Role;
use App\Models\User;

class AssignableRoles
{
    public static function isSuperAdmin(?User $actor): bool
    {
        return (bool) $actor?->hasRole(Role::SUPER_ADMIN);
    }

    /**
     * Role id => name, limited to what the actor may assign.
     *
     * @return array<int, string>
     */
    public static function optionsFor(?User $actor): array
    {
        return Role::query()
            ->when(
                ! self::isSuperAdmin($actor),
                fn ($query) => $query->whereNotIn('name', Role::PRIVILEGED),
            )
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }
}
