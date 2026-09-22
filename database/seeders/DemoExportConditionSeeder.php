<?php

/**
 * File: database/seeders/DemoExportConditionSeeder.php
 * Responsibility: Seeds export shipments that cover the remaining states.
 * What it does:
 * - Adds a draft, a document-received start, an on-the-way-to-factory leg, a
 *   stuffing/checking-PEB case, a gate-in-CY case and a cancelled shipment, so
 *   every export milestone, status and shipment mode has demo data.
 * - Finds companies by code, so it depends on DemoCompanySeeder running first.
 * - Idempotent: B/L numbers are unique and progress fields (status,
 *   current_milestone) are only written when the record is first created, so
 *   re-running never rewinds a live shipment.
 * How to use: run by DatabaseSeeder after DemoExportShipmentSeeder.
 * How to extend: add a shipment row; keep B/L numbers unique.
 */

namespace Database\Seeders;

use App\Enums\ContainerStatus;
use App\Enums\ExportMilestone;
use App\Enums\ShipmentMode;
use App\Enums\ShipmentStatus;
use App\Enums\StuffingStatus;
use App\Models\Company;
use App\Models\ExportContainer;
use App\Models\ExportShipment;
use App\Models\HsCode;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoExportConditionSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedDraftShipment();
        $this->seedEarlyAndMidMilestones();
        $this->seedCancelledShipment();
    }

    /**
     * A shipment still in the draft state: the portal hides drafts, so this is
     * the record that proves internal-only visibility.
     */
    private function seedDraftShipment(): void
    {
        $shipment = $this->shipment('JRD', [
            'bl_number' => 'BL-EXP-DRAFT',
            'shipment_mode' => ShipmentMode::Lcl,
            'shipping_line' => 'Samudera',
            'vessel_name' => 'MV Nusantara Line',
            'voyage_number' => 'V-D01',
            'port_of_loading' => 'Surabaya (IDSUB)',
            'port_of_discharge' => 'Port Klang (MYPKG)',
        ], [
            'status' => ShipmentStatus::Draft,
            'current_milestone' => ExportMilestone::DocumentReceived,
        ]);

        $this->hsCodes($shipment, '9403.60');
    }

    /**
     * Two shipments early in the flow: one just after the booking order is
     * checked, one already moving to the factory. Together they exercise the
     * document/booking fields that later milestones no longer edit.
     */
    private function seedEarlyAndMidMilestones(): void
    {
        $booking = $this->shipment('BJM', [
            'bl_number' => 'BL-EXP-0004',
            'shipment_mode' => ShipmentMode::Fcl,
            'shipping_line' => 'Evergreen',
            'vessel_name' => 'MV Ever Genius',
            'voyage_number' => 'V-204',
            'port_of_loading' => 'Balikpapan (IDBPN)',
            'port_of_discharge' => 'Shanghai (CNSHA)',
            'eta_at' => now()->addDays(21),
        ], [
            'current_milestone' => ExportMilestone::CheckingBookingOrder,
        ]);

        $this->hsCodes($booking, '4407.99');

        $this->container($booking, 'EGHU9001001', '40', 'SL-0101', [
            'status' => ContainerStatus::Pending,
        ]);

        $hauling = $this->shipment('NUS', [
            'bl_number' => 'BL-EXP-0005',
            'shipment_mode' => ShipmentMode::Fcl,
            'shipping_line' => 'Maersk',
            'vessel_name' => 'MV Ocean Voyager',
            'voyage_number' => 'V-330',
            'port_of_loading' => 'Jakarta (IDJKT)',
            'port_of_discharge' => 'Singapore (SGSIN)',
            'pickup_depot_name' => 'Depot Tanjung Priok',
            'eta_at' => now()->addDays(7),
        ], [
            'current_milestone' => ExportMilestone::OnTheWayToFactory,
        ]);

        $this->hsCodes($hauling, '8504.40');

        $this->container($hauling, 'MSKU9002002', '40', 'SL-0102', [
            'driver_name' => 'Rudi Hartono',
            'license_number' => 'B 9021 XY',
            'tracking_position' => 'Leaving depot, en route to factory',
            'status' => ContainerStatus::InProgress,
        ]);
    }

    /**
     * A cancelled shipment with a stuffing-PEB and a gate-in-CY container, so
     * the cancelled status badge and the partial stuffing data both appear.
     */
    private function seedCancelledShipment(): void
    {
        $shipment = $this->shipment('SNI', [
            'bl_number' => 'BL-EXP-0006',
            'shipment_mode' => ShipmentMode::Air,
            'aju_number' => 'AJU-EXP-0006',
            'shipping_line' => 'Air Cargo',
            'vessel_name' => 'GA-402',
            'voyage_number' => 'V-402',
            'port_of_loading' => 'Makassar (IDUPG)',
            'port_of_discharge' => 'Tokyo (NRT)',
            'stuffing_date' => now()->subDays(9),
            'stuffing_destination' => 'Gudang SNI, Makassar',
        ], [
            'status' => ShipmentStatus::Cancelled,
            'current_milestone' => ExportMilestone::CheckingPebNpe,
        ]);

        $this->hsCodes($shipment, '2604.00');

        $this->container($shipment, 'AIRU9003003', '20', 'SL-0103', [
            'stuffing_status' => StuffingStatus::OnProcess,
            'status' => ContainerStatus::Cancelled,
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
    private function shipment(string $companyCode, array $attributes, array $initial = []): ExportShipment
    {
        $company = Company::query()->where('code', $companyCode)->firstOrFail();

        $shipment = ExportShipment::query()->firstOrNew(['bl_number' => $attributes['bl_number']]);

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
    private function container(ExportShipment $shipment, string $number, string $size, string $seal, array $initial = []): void
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
}
