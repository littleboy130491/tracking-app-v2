<?php

/**
 * File: database/seeders/SuperAdminSeeder.php
 * Responsibility: Creates the default staff accounts.
 * What it does:
 * - Creates a super admin (bypasses every gate and is the only role that may
 *   create or edit admins), an admin and an operator for testing the granular
 *   permission model.
 * How to use: `php artisan db:seed --class=SuperAdminSeeder`.
 * How to extend: Add more staff accounts; never hardcode production passwords.
 */

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            'superadmin@example.com' => ['Super Admin', Role::SUPER_ADMIN],
            'admin@example.com' => ['Admin', Role::ADMIN],
            'operator@example.com' => ['Operator', Role::OPERATOR],
        ];

        foreach ($accounts as $email => [$name, $role]) {
            $user = User::query()->updateOrCreate(
                ['email' => $email],
                ['name' => $name, 'password' => 'password', 'is_active' => true],
            );

            $user->syncRoles([$role]);
        }
    }
}
