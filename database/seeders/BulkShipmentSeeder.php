<?php

/**
 * File: database/seeders/BulkShipmentSeeder.php
 * Responsibility: Seeds bulk shipments for pagination checking.
 * What it does:
 * - Creates 25 export (BL-EXP-BULK-001..025) and 25 import (BL-IMP-BULK-001..025)
 *   shipments spread across companies and mid-process milestones, each with
 *   one or two containers. Everything stays in progress so the completed demo
 *   records (and their tests) are untouched.
 * - Idempotent: firstOrNew by B/L number, progress fields only on creation.
 * How to use: STANDALONE only — `php artisan db:seed --class=BulkShipmentSeeder`.
 *   Deliberately NOT wired into DatabaseSeeder: the test suite seeds the full
 *   database fresh per test and bulk rows would only slow it down.
 * How to extend: bump the per-process count below; keep B/L numbers unique.
 */

namespace Database\Seeders;

use App\Enums\ExportMilestone;
use App\Enums\ImportMilestone;
use App\Enums\ShipmentMode;
use App\Enums\ShipmentStatus;
use App\Models\Company;
use App\Models\ExportContainer;
use App\Models\ExportShipment;
use App\Models\ImportContainer;
use App\Models\ImportShipment;
use App\Models\User;
use Illuminate\Database\Seeder;

class BulkShipmentSeeder extends Seeder
{
    private const COUNT = 25;

    /** Mid-process milestones, cycled per row (never the first or final step). */
    private const EXPORT_MILESTONES = [1, 2, 3, 4, 5, 6];

    private const IMPORT_MILESTONES = [1, 2, 4, 6, 8, 10];

    public function run(): void
    {
        $companies = Company::query()->orderBy('code')->get();
        $adminId = User::query()->where('email', 'admin@example.com')->value('id');

        $exportSequence = ExportMilestone::sequence();
        $importSequence = ImportMilestone::sequence();

        for ($i = 1; $i <= self::COUNT; $i++) {
            $company = $companies[($i - 1) % $companies->count()];
            $suffix = sprintf('%03d', $i);

            $export = ExportShipment::query()->firstOrNew(['bl_number' => 'BL-EXP-BULK-'.$suffix]);
            $export->fill([
                'shipment_mode' => ShipmentMode::Fcl,
                'shipping_line' => 'Bulk Line',
                'vessel_name' => 'MV Bulk Carrier',
                'voyage_number' => 'V-'.(100 + $i),
                'port_of_loading' => 'Jakarta (IDJKT)',
                'port_of_discharge' => 'Singapore (SGSIN)',
                'company_id' => $company->getKey(),
                'company_name_snapshot' => $company->name,
            ]);

            if (! $export->exists) {
                $export->fill([
                    'status' => ShipmentStatus::InProgress,
                    'current_milestone' => $exportSequence[self::EXPORT_MILESTONES[($i - 1) % count(self::EXPORT_MILESTONES)]],
                    'document_received_date' => today()->subDays(30 - $i),
                    'document_received_by' => $adminId,
                ]);
            }

            $export->save();

            // Odd rows carry one container, even rows two.
            for ($j = 1; $j <= 1 + ($i % 2); $j++) {
                $container = ExportContainer::query()->firstOrNew([
                    'export_shipment_id' => $export->getKey(),
                    'container_number' => 'BKEU'.sprintf('%05d', $i).$j,
                ]);

                $container->fill([
                    'size' => $i % 2 === 0 ? '40' : '20',
                    'seal_number' => 'SL-BE-'.$suffix.$j,
                ]);

                $container->save();
            }

            $import = ImportShipment::query()->firstOrNew(['bl_number' => 'BL-IMP-BULK-'.$suffix]);
            $import->fill([
                'shipment_mode' => ShipmentMode::Fcl,
                'shipping_line' => 'Bulk Line',
                'vessel_name' => 'MV Bulk Trader',
                'voyage_number' => 'V-'.(200 + $i),
                'port_of_loading' => 'Shanghai (CNSHA)',
                'port_of_discharge' => 'Surabaya (IDSUB)',
                'company_id' => $company->getKey(),
                'company_name_snapshot' => $company->name,
            ]);

            if (! $import->exists) {
                $import->fill([
                    'status' => ShipmentStatus::InProgress,
                    'current_milestone' => $importSequence[self::IMPORT_MILESTONES[($i - 1) % count(self::IMPORT_MILESTONES)]],
                    'document_received_date' => today()->subDays(30 - $i),
                    'document_received_by' => $adminId,
                ]);
            }

            $import->save();

            for ($j = 1; $j <= 1 + ($i % 2); $j++) {
                $container = ImportContainer::query()->firstOrNew([
                    'import_shipment_id' => $import->getKey(),
                    'container_number' => 'BKIU'.sprintf('%05d', $i).$j,
                ]);

                $container->fill([
                    'size' => $i % 2 === 0 ? '40' : '20',
                    'seal_number' => 'SL-BI-'.$suffix.$j,
                ]);

                $container->save();
            }
        }
    }
}
