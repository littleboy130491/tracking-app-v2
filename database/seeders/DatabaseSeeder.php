<?php

/**
 * File: database/seeders/DatabaseSeeder.php
 * Responsibility: Orchestrates database seeding.
 * What it does:
 * - Runs the role, staff and demo seeders.
 * How to use: `php artisan migrate:fresh --seed`.
 * How to extend: Append new seeders to the call list in the right order.
 */

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
            SuperAdminSeeder::class,
            HsCodeSeeder::class,
            DemoCompanySeeder::class,
            DemoImajinerUserSeeder::class,
            DemoExportShipmentSeeder::class,
            DemoImportShipmentSeeder::class,
            DemoSpjmShipmentSeeder::class,
            DemoExportConditionSeeder::class,
            DemoImportConditionSeeder::class,
            DemoContainerConditionSeeder::class,
            DemoAttachmentSeeder::class,
            DemoActivityLogSeeder::class,
        ]);
    }
}
