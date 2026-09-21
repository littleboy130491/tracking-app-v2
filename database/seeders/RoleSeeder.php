<?php

/**
 * File: database/seeders/RoleSeeder.php
 * Responsibility: Creates the application roles and their permissions.
 * What it does:
 * - Creates super_admin, admin, operator and customer with the internal flag.
 * - super_admin holds every permission (and also bypasses every gate via
 *   Shield). admin holds everything except the permanent-delete permissions
 *   (`ForceDelete*`): an admin soft-deletes and restores, but never purges.
 * - Operators work shipments only: no company/user/role administration and
 *   no delete lifecycle at all. Customers are portal-only.
 * - Shield permissions only exist after `php artisan shield:generate`, so the
 *   grants are skipped (and can be re-run) on a database that has none yet.
 * How to use: `php artisan db:seed --class=RoleSeeder` (safe to re-run).
 * How to extend: Add a role name constant to App\Models\Role and a row here.
 */

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [Role::SUPER_ADMIN, true],
            [Role::ADMIN, true],
            [Role::OPERATOR, true],
            [Role::CUSTOMER, false],
        ];

        foreach ($roles as [$name, $isInternal]) {
            Role::query()->updateOrCreate(
                ['name' => $name, 'guard_name' => 'web'],
                ['is_internal' => $isInternal],
            );
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->grantPermissions();
    }

    private function grantPermissions(): void
    {
        $permissions = Permission::query()->where('guard_name', 'web')->get();

        if ($permissions->isEmpty()) {
            return;
        }

        // super_admin additionally bypasses every gate (Shield), but holding the
        // permissions too keeps the role screen readable.
        $this->role(Role::SUPER_ADMIN)?->permissions()->sync($permissions->pluck('id'));

        // Admin may soft-delete and restore, but permanent deletion
        // (ForceDelete / ForceDeleteAny) is reserved for super_admin.
        $adminIds = $permissions
            ->reject(fn (Permission $permission): bool => str_starts_with($permission->name, 'ForceDelete'))
            ->pluck('id');

        $this->role(Role::ADMIN)?->permissions()->sync($adminIds);

        // Operators work shipments only: companies and users are admin
        // territory, and the whole delete lifecycle (delete, restore, force
        // delete) is out of reach.
        $operatorIds = $permissions
            ->reject(fn (Permission $permission): bool => str_contains($permission->name, 'User')
                || str_contains($permission->name, 'Role')
                || str_contains($permission->name, 'Company')
                || str_contains($permission->name, 'Delete')
                || str_contains($permission->name, 'Restore'))
            ->pluck('id');

        $this->role(Role::OPERATOR)?->permissions()->sync($operatorIds);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function role(string $name): ?Role
    {
        return Role::query()->where('name', $name)->where('guard_name', 'web')->first();
    }
}
