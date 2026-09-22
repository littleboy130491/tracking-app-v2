<?php

/**
 * File: database/seeders/DemoSpjmShipmentSeeder.php
 * Responsibility: Seeds demo import shipments that demonstrate the SPJM branch.
 * What it does:
 * - Creates one completed SPJM shipment (the whole additional customs branch
 *   is done) and one that just received its SPJM response, so the stepper's
 *   red block can be seen both finished and upcoming.
 * - Each shipment's milestone matches the data seeded for it; the SPJM steps
 *   only exist while `billing_response` is SPJM.
 * - Containers carry their own driver/tracking data, including a validated
 *   tracking URL for the container on the way to the factory.
 * - Finds companies by code, so it depends on DemoCompanySeeder running first.
 * - Idempotent: B/L numbers are unique and progress fields are only written
 *   when the record is first created.
 * How to use: run by DatabaseSeeder after DemoImportShipmentSeeder; run alone
 *   with `php artisan db:seed --class=DemoSpjmShipmentSeeder`.
 * How to extend: add another SPJM shipment row; keep B/L numbers unique.
 */

namespace Database\Seeders;

use App\Enums\BillingIssuanceStatus;
use App\Enums\BillingResponse;
use App\Enums\ContainerStatus;
use App\Enums\FactoryLoadingStatus;
use App\Enums\ImportMilestone;
use App\Enums\ShipmentStatus;
use App\Models\Company;
use App\Models\HsCode;
use App\Models\ImportContainer;
use App\Models\ImportShipment;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoSpjmShipmentSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedCompletedSpjmShipment();
        $this->seedFreshSpjmShipment();
    }

    /**
     * An SPJM shipment whose additional steps are all done: it sits on the
     * final milestone, which is what completes a shipment.
     */
    private function seedCompletedSpjmShipment(): void
    {
        $completed = $this->shipment('SIN', [
            'bl_number' => 'BL-IMP-0003',
            'aju_number' => 'AJU-0003',
            'shipping_line' => 'Maersk',
            'vessel_name' => 'MV Straits Pioneer',
            'voyage_number' => 'V-880',
            'port_of_loading' => 'Shanghai (CNSHA)',
            'port_of_discharge' => 'Surabaya (IDSUB)',
            'departure_date' => now()->subDays(25)->toDateString(),
            'eta_at' => now()->subDays(9),
            'goods_description' => 'Textile machinery',
            'packages' => '64 crates',
            'terminal_name' => 'Terminal Petikemas Surabaya (TPS)',
            'loading_date' => now()->subDays(6)->toDateString(),
            'loading_destination' => 'Gudang SIN, Surabaya',
            'billing_issuance_status' => BillingIssuanceStatus::Issued,
            // The customer confirmed the draft PIB during the process.
            'confirmation_checklist' => true,
            // SPJM: the additional customs steps ran end to end.
            'billing_response' => BillingResponse::Spjm,
        ], [
            'status' => ShipmentStatus::Completed,
            'completed_at' => now()->subDays(4),
            'current_milestone' => ImportMilestone::EmptyReturned,
        ]);

        $this->hsCodes($completed, '8448.20');

        $this->container($completed, 'MSKU7788990', '40', [
            'gate_out_cy_at' => now()->subDays(7),
            'driver_name' => 'Slamet Riyadi',
            'license_number' => 'L 9021 UZ',
            'tracking_position' => 'Driver Slamet — live location shared',
            'gross_weight' => 24800,
            'cbm' => 58.4,
            'factory_loading_status' => FactoryLoadingStatus::Finished,
            'return_depot_name' => 'Depot Surabaya Barat',
            'empty_returned_at' => now()->subDays(4),
            'status' => ContainerStatus::Completed,
            'completed_at' => now()->subDays(4),
        ]);

        $this->container($completed, 'MSKU7788991', '20', [
            'gate_out_cy_at' => now()->subDays(7),
            'driver_name' => 'Agus Salim',
            'license_number' => 'L 8842 XX',
            'tracking_position_url' => 'https://maps.example.com/live/MSKU7788991',
            'gross_weight' => 14250,
            'cbm' => 29.1,
            'factory_loading_status' => FactoryLoadingStatus::Finished,
            'return_depot_name' => 'Depot Surabaya Barat',
            'empty_returned_at' => now()->subDays(4),
            'status' => ContainerStatus::Completed,
            'completed_at' => now()->subDays(4),
        ]);
    }

    /**
     * An SPJM shipment right at "Response billing": the red block on the
     * stepper is upcoming, and its containers only carry what the response
     * step unlocks (number, no size yet).
     */
    private function seedFreshSpjmShipment(): void
    {
        $fresh = $this->shipment('BJM', [
            'bl_number' => 'BL-IMP-0004',
            'aju_number' => 'AJU-0004',
            'shipping_line' => 'PIL',
            'vessel_name' => 'MV Selatan Jaya',
            'voyage_number' => 'V-311',
            'port_of_loading' => 'Kaohsiung (TWKHH)',
            'port_of_discharge' => 'Surabaya (IDSUB)',
            'departure_date' => now()->subDays(15)->toDateString(),
            'eta_at' => now()->addDays(9),
            'billing_issuance_status' => BillingIssuanceStatus::Issued,
            // The customer confirmed the draft PIB before the PIB was lodged.
            'confirmation_checklist' => true,
            // The SPJM response puts this shipment on the additional steps.
            'billing_response' => BillingResponse::Spjm,
        ], [
            'current_milestone' => ImportMilestone::ResponseBilling,
        ]);

        $this->container($fresh, 'PILU5566771');
        $this->container($fresh, 'PILU5566772');
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
     * Containers keep whatever progress they have: the size is descriptive
     * (left null while the step that unlocks it is still ahead), `$initial`
     * progress fields only apply on creation.
     *
     * @param  array<string, mixed>  $initial
     */
    private function container(ImportShipment $shipment, string $number, ?string $size = null, array $initial = []): void
    {
        $container = ImportContainer::query()->firstOrNew([
            'import_shipment_id' => $shipment->getKey(),
            'container_number' => $number,
        ]);

        if ($size !== null) {
            $container->fill(['size' => $size]);
        }

        if (! $container->exists) {
            $container->fill($initial);
        }

        $container->save();
    }
}
