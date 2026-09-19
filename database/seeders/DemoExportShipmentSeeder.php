<?php

/**
 * File: database/seeders/DemoExportShipmentSeeder.php
 * Responsibility: Seeds demo export shipments and their containers.
 * What it does:
 * - Creates two in-progress export shipments and one completed example, so the
 *   admin and the portal have live and finished records to look at.
 * - Finds companies by code, so it depends on DemoCompanySeeder running first
 *   and must not assume how many companies exist.
 * - Idempotent: reference numbers are unique and progress fields are only
 *   written when the record is first created, so re-running never resets
 *   anyone's work.
 * How to use: run by DatabaseSeeder after DemoCompanySeeder.
 * How to extend: add a shipment row; keep reference numbers unique.
 */

namespace Database\Seeders;

use App\Enums\ContainerStatus;
use App\Enums\ShipmentMode;
use App\Enums\ShipmentStatus;
use App\Enums\StuffingStatus;
use App\Models\Company;
use App\Models\ExportContainer;
use App\Models\ExportShipment;
use App\Models\HsCode;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoExportShipmentSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedInProgressShipments();
        $this->seedCompletedShipment();
    }

    /**
     * Shipments left part-way through, so the admin has live work.
     */
    private function seedInProgressShipments(): void
    {
        $nusantara = $this->shipment('NUS', [
            'reference_number' => 'REF-EXP-0001',
            'bl_number' => 'BL-EXP-0001',
            'shipment_mode' => ShipmentMode::Fcl,
            'shipping_line' => 'Maersk',
            'vessel_name' => 'MV Ocean Star',
            'voyage_number' => 'V-001',
            'port_of_loading' => 'Jakarta (IDJKT)',
            'port_of_discharge' => 'Singapore (SGSIN)',
            'eta_at' => now()->addDays(10),
            'goods_description' => 'Furniture parts',
        ]);

        $this->hsCodes($nusantara, '9403.60');

        $this->container($nusantara, 'MSKU1234567', '40', 'HC', 'SL-0001');
        $this->container($nusantara, 'MSKU1234568', '40', 'GP', 'SL-0002');

        $borneo = $this->shipment('BJM', [
            'reference_number' => 'REF-EXP-0002',
            'bl_number' => 'BL-EXP-0002',
            'shipment_mode' => ShipmentMode::Fcl,
            'shipping_line' => 'PIL',
            'vessel_name' => 'MV Bintulu Trader',
            'voyage_number' => 'V-310',
            'port_of_loading' => 'Balikpapan (IDBPN)',
            'port_of_discharge' => 'Kaohsiung (TWKHH)',
            'eta_at' => now()->addDays(14),
            'goods_description' => 'Processed timber',
        ]);

        $this->hsCodes($borneo, '4407.99');

        $this->container($borneo, 'PILU4455661', '40', 'GP', 'SL-0005');
    }

    /**
     * One export shipment whose data is already filled end to end.
     */
    private function seedCompletedShipment(): void
    {
        $exported = $this->shipment('SNI', [
            'reference_number' => 'REF-EXP-0003',
            'bl_number' => 'BL-EXP-0003',
            'shipment_mode' => ShipmentMode::Fcl,
            'shipping_line' => 'Evergreen',
            'vessel_name' => 'MV Ever Summit',
            'voyage_number' => 'V-512',
            'port_of_loading' => 'Makassar (IDUPG)',
            'port_of_discharge' => 'Busan (KRPUS)',
            'depot_closing_at' => now()->subDays(24),
            'cy_closing_at' => now()->subDays(23),
            'departure_date' => now()->subDays(20)->toDateString(),
            'eta_at' => now()->subDays(6),
            'actual_arrival_at' => now()->subDays(6),
            'goods_description' => 'Nickel ore concentrate',
            'pickup_depot_name' => 'Depot Makassar Utama',
            'stuffing_date' => now()->subDays(24)->toDateString(),
            'stuffing_destination' => 'PT Smelter Makassar, Bantaeng',
        ], [
            'status' => ShipmentStatus::Completed,
            'completed_at' => now()->subDays(6),
        ]);

        $this->hsCodes($exported, '2604.00');

        $this->container($exported, 'EGHU6677881', '40', 'GP', 'SL-0006', [
            'driver_name' => 'Andi Pratama',
            'license_number' => 'DD 8123 KK',
            'tracking_position' => 'Passed Maros checkpoint',
            'stuffing_status' => StuffingStatus::Finished,
            'stuffing_started_at' => now()->subDays(24),
            'stuffing_finished_at' => now()->subDays(23)->subHours(8),
            'gate_in_port_name' => 'Makassar New Port',
            'gate_in_cy_at' => now()->subDays(23),
            'vgm_value' => 30250,
            'final_checked' => true,
            'final_checked_at' => now()->subDays(22),
            'status' => ContainerStatus::Completed,
            'completed_at' => now()->subDays(23),
        ]);
    }

    /**
     * Create or refresh a shipment. `$initial` holds progress fields (status,
     * completed_at) written only on creation so re-seeding never rewinds a
     * live shipment.
     *
     * @param  array<string, mixed>  $attributes
     * @param  array<string, mixed>  $initial
     */
    private function shipment(string $companyCode, array $attributes, array $initial = []): ExportShipment
    {
        $company = Company::query()->where('code', $companyCode)->firstOrFail();

        $shipment = ExportShipment::query()->firstOrNew(['reference_number' => $attributes['reference_number']]);

        $shipment->fill($attributes + [
            'company_id' => $company->getKey(),
            'company_name_snapshot' => $company->name,
        ]);

        if (! $shipment->exists) {
            // Model events are disabled during seeding, so the Document
            // Received defaults are written here as well.
            $shipment->fill($initial + [
                'status' => ShipmentStatus::InProgress,
                'document_received_date' => today(),
                'document_received_by' => User::query()->where('email', 'admin@example.com')->value('id'),
            ]);
        }

        $shipment->save();

        return $shipment;
    }

    /**
     * Attach HS codes to a shipment; sync is idempotent.
     */
    private function hsCodes(ExportShipment $shipment, string ...$codes): void
    {
        $ids = HsCode::query()->whereIn('code', $codes)->pluck('id');

        $shipment->hsCodes()->sync($ids);
    }

    /**
     * Containers keep whatever progress they have: descriptive fields are
     * refreshed, `$initial` progress fields only apply on creation.
     *
     * @param  array<string, mixed>  $initial
     */
    private function container(ExportShipment $shipment, string $number, string $size, string $type, string $seal, array $initial = []): void
    {
        $container = ExportContainer::query()->firstOrNew([
            'export_shipment_id' => $shipment->getKey(),
            'container_number' => $number,
        ]);

        $container->fill([
            'size' => $size,
            'type' => $type,
            'seal_number' => $seal,
        ]);

        if (! $container->exists) {
            $container->fill($initial);
        }

        $container->save();
    }
}
