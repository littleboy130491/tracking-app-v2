<?php

/**
 * File: database/seeders/DemoContainerConditionSeeder.php
 * Responsibility: Seeds containers that cover every container status.
 * What it does:
 * - Attaches containers in `pending`, `in_progress`, `completed` and
 *   `cancelled` to existing demo shipments, so each container status badge,
 *   colour and filter has data on both the export and import sides.
 * - Looks its shipments up by B/L number, so it depends on the shipment
 *   seeders running first.
 * - Idempotent: container numbers are unique per shipment and progress fields
 *   are only written when the container is first created.
 * How to use: run by DatabaseSeeder after the shipment/condition seeders.
 * How to extend: add a row; keep container numbers unique.
 */

namespace Database\Seeders;

use App\Enums\ContainerStatus;
use App\Models\ExportContainer;
use App\Models\ExportShipment;
use App\Models\ImportContainer;
use App\Models\ImportShipment;
use Illuminate\Database\Seeder;

class DemoContainerConditionSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedExportContainers();
        $this->seedImportContainers();
    }

    /**
     * Extra export containers: a pending and an in-progress one on a live
     * shipment, each carrying the progress fields its status implies.
     */
    private function seedExportContainers(): void
    {
        $shipment = ExportShipment::query()->where('bl_number', 'BL-EXP-0005')->first();

        if ($shipment === null) {
            return;
        }

        $this->exportContainer($shipment, 'MSKU9002003', '40', 'SL-0104', [
            'status' => ContainerStatus::Pending,
        ]);

        $this->exportContainer($shipment, 'MSKU9002004', '40', 'SL-0105', [
            'driver_name' => 'Bayu Setiawan',
            'license_number' => 'B 7788 QW',
            'tracking_position' => 'At factory, stuffing next',
            'status' => ContainerStatus::InProgress,
        ]);
    }

    /**
     * Extra import containers: an in-progress one and a cancelled one, so the
     * import side shows every status too.
     */
    private function seedImportContainers(): void
    {
        $shipment = ImportShipment::query()->where('bl_number', 'BL-IMP-0001')->first();

        if ($shipment === null) {
            return;
        }

        $this->importContainer($shipment, 'CMAU7654323', '20', [
            'description_of_goods' => 'Electronic components',
            'packages' => '85 cartons',
            'status' => ContainerStatus::InProgress,
        ]);

        $this->importContainer($shipment, 'CMAU7654324', '20', [
            'description_of_goods' => 'Electronic components',
            'packages' => '85 cartons',
            'status' => ContainerStatus::Cancelled,
        ]);
    }

    /**
     * @param  array<string, mixed>  $initial
     */
    private function exportContainer(ExportShipment $shipment, string $number, string $size, string $seal, array $initial = []): void
    {
        $container = ExportContainer::query()->firstOrNew([
            'export_shipment_id' => $shipment->getKey(),
            'container_number' => $number,
        ]);

        $container->fill([
            'size' => $size,
            'seal_number' => $seal,
        ]);

        if (! $container->exists) {
            $container->fill($initial);
        }

        $container->save();
    }

    /**
     * @param  array<string, mixed>  $initial
     */
    private function importContainer(ImportShipment $shipment, string $number, string $size, array $initial = []): void
    {
        $container = ImportContainer::query()->firstOrNew([
            'import_shipment_id' => $shipment->getKey(),
            'container_number' => $number,
        ]);

        $container->fill([
            'size' => $size,
        ]);

        if (! $container->exists) {
            $container->fill($initial);
        }

        $container->save();
    }
}
