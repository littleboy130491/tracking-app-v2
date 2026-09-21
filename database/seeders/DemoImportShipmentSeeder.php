<?php

/**
 * File: database/seeders/DemoImportShipmentSeeder.php
 * Responsibility: Seeds demo import shipments and their containers.
 * What it does:
 * - Creates one in-progress import shipment on the SPJM response (so that
 *   branch is demonstrable) and one completed example that settled on SPPB.
 *   Each shipment's milestone reflects the data seeded for it.
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
use App\Enums\BillingResponse;
use App\Enums\ContainerStatus;
use App\Enums\ImportMilestone;
use App\Enums\ShipmentStatus;
use App\Models\Company;
use App\Models\HsCode;
use App\Models\ImportContainer;
use App\Models\ImportShipment;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoImportShipmentSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedInProgressShipment();
        $this->seedCompletedShipment();
    }

    /**
     * An import shipment left on the SPJM branch, so the admin has live work:
     * its documents, billing response and HS codes are filled, which lands it
     * at "waiting process bahandle".
     */
    private function seedInProgressShipment(): void
    {
        $sinar = $this->shipment('SIN', [
            'bl_number' => 'BL-IMP-0001',
            'aju_number' => 'AJU-0001',
            'shipping_line' => 'CMA CGM',
            'vessel_name' => 'MV Southern Cross',
            'voyage_number' => 'V-777',
            'port_of_loading' => 'Shanghai (CNSHA)',
            'port_of_discharge' => 'Surabaya (IDSUB)',
            'eta_at' => now()->addDays(5),
            'goods_description' => 'Electronic components',
            'billing_issuance_status' => BillingIssuanceStatus::Issued,
            // SPJM keeps this shipment on the behandle branch.
            'billing_response' => BillingResponse::Spjm,
        ], [
            'current_milestone' => ImportMilestone::WaitingProcessBehandle,
        ]);

        $this->hsCodes($sinar, '8542.31');

        $this->container($sinar, 'CMAU7654321', '20');
        $this->container($sinar, 'CMAU7654322', '20');
    }

    /**
     * One import shipment whose data is already filled end to end: it came
     * through the SPJM branch and settled on SPPB, and sits on the final step,
     * which is what completes a shipment.
     */
    private function seedCompletedShipment(): void
    {
        $imported = $this->shipment('JRD', [
            'bl_number' => 'BL-IMP-0002',
            'aju_number' => 'AJU-0002',
            'shipping_line' => 'ONE',
            'vessel_name' => 'MV One Meridian',
            'voyage_number' => 'V-204',
            'port_of_loading' => 'Ningbo (CNNGB)',
            'port_of_discharge' => 'Surabaya (IDSUB)',
            'departure_date' => now()->subDays(18)->toDateString(),
            'eta_at' => now()->subDays(4),
            'goods_description' => 'Household appliances',
            'billing_issuance_status' => BillingIssuanceStatus::Issued,
            // The customer confirmed the draft PIB during the process.
            'confirmation_checklist' => true,
            // Came through SPJM and settled on SPPB.
            'billing_response' => BillingResponse::Sppb,
        ], [
            'status' => ShipmentStatus::Completed,
            'completed_at' => now()->subDays(2),
            'current_milestone' => ImportMilestone::EmptyReturned,
        ]);

        $this->hsCodes($imported, '8450.11');

        $this->container($imported, 'ONEU9988771', '20', [
            'gate_out_cy_at' => now()->subDays(3),
            'tracking_position' => 'Driver Budi — live location shared',
            'gross_weight' => 18500,
            'gross_weight_unit' => 'kg',
            'cbm' => 33.2,
            'factory_loading_at' => now()->subDays(3)->addHours(2),
            'empty_returned_at' => now()->subDays(2),
            'status' => ContainerStatus::Completed,
            'completed_at' => now()->subDays(2),
        ]);
    }

    /**
     * Create or refresh a shipment. `$initial` holds progress fields (status,
     * completed_at, current_milestone) written only on creation so re-seeding
     * never rewinds a live shipment.
     *
     * @param  array<string, mixed>  $attributes
     * @param  array<string, mixed>  $initial
     */
    private function shipment(string $companyCode, array $attributes, array $initial = []): ImportShipment
    {
        $company = Company::query()->where('code', $companyCode)->firstOrFail();

        $shipment = ImportShipment::query()->firstOrNew(['bl_number' => $attributes['bl_number']]);

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
    private function hsCodes(ImportShipment $shipment, string ...$codes): void
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
    private function container(ImportShipment $shipment, string $number, string $size, array $initial = []): void
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
