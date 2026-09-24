<?php

/**
 * File: database/seeders/DemoImajinerUserSeeder.php
 * Responsibility: Creates the imajiner staff account.
 * What it does:
 * - Creates webmaster.imajiner@gmail.com (name "imajiner") with the operator
 *   role, linked to every company so the admin panel row scope never hides a
 *   shipment from this account. Change the single syncRoles() line for a
 *   different role.
 * - Idempotent: updateOrCreate by email plus syncs, so re-running changes
 *   nothing.
 * How to use: run by DatabaseSeeder after DemoCompanySeeder (needs companies).
 */

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoImajinerUserSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()->updateOrCreate(
            ['email' => 'webmaster.imajiner@gmail.com'],
            ['name' => 'imajiner', 'password' => 'password', 'is_active' => true],
        );

        $user->syncRoles([Role::OPERATOR]);

        $user->companies()->sync(Company::query()->pluck('id')->all());
    }
}
