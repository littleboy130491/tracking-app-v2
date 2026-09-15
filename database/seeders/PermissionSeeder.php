<?php

/**
 * File: database/seeders/PermissionSeeder.php
 * Responsibility: Creates the Shield permission rows for the admin panel.
 * What it does:
 * - Asks Filament Shield for every permission the admin panel's resources,
 *   pages and widgets define and creates any that are missing, so a fresh
 *   install (`migrate:fresh --seed`) has a complete permission set.
 * - Idempotent: safe to re-run after adding a resource.
 * How to use: run by DatabaseSeeder before RoleSeeder (which grants the operator
 *   role its permissions); or `php artisan db:seed --class=PermissionSeeder`.
 * How to extend: also run `php artisan shield:generate --all --panel=admin` when
 *   a new resource needs a policy.
 */

namespace Database\Seeders;

use BezhanSalleh\FilamentShield\Facades\FilamentShield;
use Filament\Facades\Filament;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        Filament::setCurrentPanel('admin');

        foreach (FilamentShield::getEntitiesPermissions() as $key) {
            Permission::query()->firstOrCreate([
                'name' => $key,
                'guard_name' => 'web',
            ]);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
