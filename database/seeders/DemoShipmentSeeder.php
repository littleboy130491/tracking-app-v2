<?php

/**
 * File: database/seeders/DemoShipmentSeeder.php
 * Responsibility: Seeds demo shipments and containers.
 * What it does:
 * - Creates in-progress bills of lading (one import on the SPJM response so
 *   that branch is demonstrable) and two already-completed shipments, so the
 *   admin and the portal have finished examples to look at.
 * - Finds companies by code, so it depends on DemoCompanySeeder running first
 *   and must not assume how many companies exist.
 * - Idempotent: reference numbers are unique and progress fields are only
 *   written when the record is first created, so re-running never resets
 *   anyone's work.
 * How to use: run by DatabaseSeeder after DemoCompanySeeder.
 * How to extend: add a shipment row; keep reference numbers unique.
 */

namespace Database\Seeders;

use App\Enums\BillingIssuanceStatus;
use App\Enums\BillingPaymentStatus;
use App\Enums\BillingResponse;
use App\Enums\BillOfLadingStatus;
use App\Enums\ContainerStatus;
use App\Enums\DraftPibConfirmationStatus;
use App\Enums\ShipmentType;
use App\Enums\StuffingStatus;
use App\Models\BillOfLading;
use App\Models\Company;
use App\Models\Container;
use App\Models\HsCode;
use Illuminate\Database\Seeder;

class DemoShipmentSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedInProgressShipments();
        $this->seedCompletedShipments();
    }

    /**
     * Shipments left part-way through, so the admin has live work.
     */
    private function seedInProgressShipments(): void
    {
        $nusantara = $this->shipment('NUS', [
            'reference_number' => 'REF-EXP-0001',
            'bl_number' => 'BL-EXP-0001',
            'shipment_type' => ShipmentType::Export,
            'shipping_line' => 'Maersk',
            'vessel_name' => 'MV Ocean Star',
            'voyage_number' => 'V-001',
            'port_of_loading' => 'Jakarta (IDJKT)',
            'port_of_discharge' => 'Singapore (SGSIN)',
            'eta_at' => now()->addDays(10),
            'goods_description' => 'Furniture parts',
            'package_count' => 120,
            'package_unit' => 'carton',
        ]);

        $this->hsCodes($nusantara, '9403.60');

        $this->container($nusantara, 'MSKU1234567', '40', 'HC', 'SL-0001');
        $this->container($nusantara, 'MSKU1234568', '40', 'GP', 'SL-0002');

        $sinar = $this->shipment('SIN', [
            'reference_number' => 'REF-IMP-0001',
            'bl_number' => 'BL-IMP-0001',
            'shipment_type' => ShipmentType::Import,
            'aju_number' => 'AJU-0001',
            'shipping_line' => 'CMA CGM',
            'vessel_name' => 'MV Southern Cross',
            'voyage_number' => 'V-777',
            'port_of_loading' => 'Shanghai (CNSHA)',
            'port_of_discharge' => 'Surabaya (IDSUB)',
            'eta_at' => now()->addDays(5),
            'goods_description' => 'Electronic components',
            'package_count' => 80,
            'package_unit' => 'pallet',
            'billing_issuance_status' => BillingIssuanceStatus::Issued,
            'billing_issued_at' => now()->subDays(2),
            'billing_payment_status' => BillingPaymentStatus::Paid,
            'billing_paid_at' => now()->subDay(),
            // SPJM keeps this shipment on the behandle branch.
            'billing_response' => BillingResponse::Spjm,
            'billing_response_at' => now()->subDay(),
        ]);

        $this->hsCodes($sinar, '8542.31');

        $this->container($sinar, 'CMAU7654321', '20', 'GP', 'SL-0003');
        $this->container($sinar, 'CMAU7654322', '20', 'GP', 'SL-0004');

        $borneo = $this->shipment('BJM', [
            'reference_number' => 'REF-EXP-0002',
            'bl_number' => 'BL-EXP-0002',
            'shipment_type' => ShipmentType::Export,
            'shipping_line' => 'PIL',
            'vessel_name' => 'MV Bintulu Trader',
            'voyage_number' => 'V-310',
            'port_of_loading' => 'Balikpapan (IDBPN)',
            'port_of_discharge' => 'Kaohsiung (TWKHH)',
            'eta_at' => now()->addDays(14),
            'goods_description' => 'Processed timber',
            'package_count' => 45,
            'package_unit' => 'bundle',
        ]);

        $this->hsCodes($borneo, '4407.99');

        $this->container($borneo, 'PILU4455661', '40', 'GP', 'SL-0005');
    }

    /**
     * Two shipments whose data is already filled end to end: one export and one
     * import that came through the SPJM branch (settled on SPPB).
     */
    private function seedCompletedShipments(): void
    {
        $exported = $this->shipment('SNI', [
            'reference_number' => 'REF-EXP-0003',
            'bl_number' => 'BL-EXP-0003',
            'shipment_type' => ShipmentType::Export,
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
            'terminal_name' => 'Terminal Petikemas Makassar',
            'goods_description' => 'Nickel ore concentrate',
            'package_count' => 240,
            'package_unit' => 'bag',
        ], [
            'status' => BillOfLadingStatus::Completed,
            'completed_at' => now()->subDays(6),
        ]);

        $this->hsCodes($exported, '2604.00');

        $this->container($exported, 'EGHU6677881', '40', 'GP', 'SL-0006', [
            'pickup_depot_name' => 'Depot Makassar Utama',
            'empty_picked_up_at' => now()->subDays(25),
            'stuffing_date' => now()->subDays(24)->toDateString(),
            'stuffing_destination' => 'PT Smelter Makassar, Bantaeng',
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

        $imported = $this->shipment('JRD', [
            'reference_number' => 'REF-IMP-0002',
            'bl_number' => 'BL-IMP-0002',
            'shipment_type' => ShipmentType::Import,
            'aju_number' => 'AJU-0002',
            'do_number' => 'DO-0002',
            'shipping_line' => 'ONE',
            'vessel_name' => 'MV One Meridian',
            'voyage_number' => 'V-204',
            'port_of_loading' => 'Ningbo (CNNGB)',
            'port_of_discharge' => 'Surabaya (IDSUB)',
            'departure_date' => now()->subDays(18)->toDateString(),
            'eta_at' => now()->subDays(4),
            'actual_arrival_at' => now()->subDays(4),
            'terminal_name' => 'Terminal Petikemas Surabaya',
            'goods_description' => 'Household appliances',
            'package_count' => 96,
            'package_unit' => 'carton',
            'billing_issuance_status' => BillingIssuanceStatus::Issued,
            'billing_issued_at' => now()->subDays(11),
            'billing_payment_status' => BillingPaymentStatus::Paid,
            'billing_paid_at' => now()->subDays(10),
            'do_released_at' => now()->subDays(8),
            // The customer confirmed the draft PIB during the process.
            'draft_pib_confirmation_status' => DraftPibConfirmationStatus::Confirmed,
            'draft_pib_confirmed_at' => now()->subDays(9),
            // Came through SPJM and settled on SPPB.
            'billing_response' => BillingResponse::Sppb,
            'billing_response_at' => now()->subDays(7),
        ], [
            'status' => BillOfLadingStatus::Completed,
            'completed_at' => now()->subDays(2),
        ]);

        $this->hsCodes($imported, '8450.11');

        $this->container($imported, 'ONEU9988771', '20', 'GP', 'SL-0007', [
            'gate_out_cy_at' => now()->subDays(3),
            'empty_returned_at' => now()->subDays(2),
            'status' => ContainerStatus::Completed,
            'completed_at' => now()->subDays(2),
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
    private function shipment(string $companyCode, array $attributes, array $initial = []): BillOfLading
    {
        $company = Company::query()->where('code', $companyCode)->firstOrFail();

        $billOfLading = BillOfLading::query()->firstOrNew(['reference_number' => $attributes['reference_number']]);

        $billOfLading->fill($attributes + [
            'company_id' => $company->getKey(),
            'company_name_snapshot' => $company->name,
        ]);

        if (! $billOfLading->exists) {
            $billOfLading->fill($initial + ['status' => BillOfLadingStatus::InProgress]);
        }

        $billOfLading->save();

        return $billOfLading;
    }

    /**
     * Attach HS codes to a shipment; sync is idempotent.
     */
    private function hsCodes(BillOfLading $billOfLading, string ...$codes): void
    {
        $ids = HsCode::query()->whereIn('code', $codes)->pluck('id');

        $billOfLading->hsCodes()->sync($ids);
    }

    /**
     * Containers keep whatever progress they have: descriptive fields are
     * refreshed, `$initial` progress fields only apply on creation.
     *
     * @param  array<string, mixed>  $initial
     */
    private function container(BillOfLading $billOfLading, string $number, string $size, string $type, string $seal, array $initial = []): void
    {
        $container = Container::query()->firstOrNew([
            'bill_of_lading_id' => $billOfLading->getKey(),
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
