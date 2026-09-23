<?php

/**
 * File: database/seeders/DemoImportConditionSeeder.php
 * Responsibility: Seeds import shipments that cover the remaining states.
 * What it does:
 * - Adds a draft, a checking-document start, a draft-PIB/confirmation case, an
 *   AP and an SPJK billing response and a cancelled shipment, so every import
 *   milestone, status and billing response has demo data.
 * - Finds companies by code, so it depends on DemoCompanySeeder running first.
 * - Idempotent: B/L numbers are unique and progress fields (status,
 *   current_milestone) are only written when the record is first created, so
 *   re-running never rewinds a live shipment.
 * How to use: run by DatabaseSeeder after DemoImportShipmentSeeder.
 * How to extend: add a shipment row; keep B/L numbers unique.
 */

namespace Database\Seeders;

use App\Enums\BillingIssuanceStatus;
use App\Enums\BillingResponse;
use App\Enums\ContainerStatus;
use App\Enums\ImportMilestone;
use App\Enums\ShipmentMode;
use App\Enums\ShipmentStatus;
use App\Models\Company;
use App\Models\HsCode;
use App\Models\ImportContainer;
use App\Models\ImportShipment;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoImportConditionSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedDraftShipment();
        $this->seedEarlyMilestones();
        $this->seedBillingResponseVariants();
        $this->seedCancelledShipment();
    }

    /**
     * A shipment still in the draft state: the portal hides drafts, so this is
     * the record that proves internal-only visibility.
     */
    private function seedDraftShipment(): void
    {
        // Drafts sit on the first milestone, so only the B/L number exists —
        // the shipping fields are still locked in the admin.
        $this->shipment('BJM', [
            'bl_number' => 'BL-IMP-DRAFT',
        ], [
            'status' => ShipmentStatus::Draft,
            'current_milestone' => ImportMilestone::DocumentReceived,
        ]);
    }

    /**
     * Two shipments early in the flow: one just checking documents, one with a
     * draft PIB awaiting the customer confirmation. These exercise the
     * document/PIB fields later milestones no longer edit.
     */
    private function seedEarlyMilestones(): void
    {
        // Checking document: only the B/L number and shipment mode are
        // unlocked — everything else lands on later milestones.
        $this->shipment('NUS', [
            'bl_number' => 'BL-IMP-0010',
            'shipment_mode' => ShipmentMode::Fcl,
        ], [
            'current_milestone' => ImportMilestone::CheckingDocument,
        ]);

        // Checking draft PIB: the shipping line and vessel name are known;
        // billing, sailing and cargo fields are still locked, and the
        // containers repeater opens at Response billing.
        $this->shipment('SNI', [
            'bl_number' => 'BL-IMP-0011',
            'shipment_mode' => ShipmentMode::Fcl,
            'shipping_line' => 'Evergreen',
            'vessel_name' => 'MV Ever Legend',
        ], [
            'current_milestone' => ImportMilestone::CheckingDraftPib,
        ]);
    }

    /**
     * Two settled-billing shipments: one answered AP, one answered SPJK. Both
     * are terminal non-SPJM outcomes, so the stepper stops before the red
     * SPJM block.
     */
    private function seedBillingResponseVariants(): void
    {
        $ap = $this->shipment('JRD', [
            'bl_number' => 'BL-IMP-0012',
            'shipment_mode' => ShipmentMode::Fcl,
            'aju_number' => 'AJU-0006',
            'shipping_line' => 'ONE',
            'vessel_name' => 'MV One Horizon',
            'voyage_number' => 'V-606',
            'port_of_loading' => 'Ningbo (CNNGB)',
            'port_of_discharge' => 'Surabaya (IDSUB)',
            'goods_description' => 'Retail fixtures',
            'packages' => '200 cartons',
            'terminal_name' => 'Terminal Petikemas Surabaya (TPS)',
            'billing_issuance_status' => BillingIssuanceStatus::Issued,
            'confirmation_checklist' => true,
            'billing_response' => BillingResponse::Ap,
        ], [
            'current_milestone' => ImportMilestone::ContainerShippingSchedule,
        ]);

        $this->hsCodes($ap, '9403.60');

        $this->container($ap, 'ONEU9106006', '20', [
            'description_of_goods' => 'Retail fixtures',
            'packages' => '200 cartons',
            'status' => ContainerStatus::InProgress,
        ]);

        $spjk = $this->shipment('SIN', [
            'bl_number' => 'BL-IMP-0013',
            'shipment_mode' => ShipmentMode::Lcl,
            'aju_number' => 'AJU-0007',
            'shipping_line' => 'CMA CGM',
            'vessel_name' => 'MV Southern Tide',
            'voyage_number' => 'V-707',
            'port_of_loading' => 'Shanghai (CNSHA)',
            'port_of_discharge' => 'Surabaya (IDSUB)',
            'goods_description' => 'Textile machinery',
            'packages' => '55 crates',
            'terminal_name' => 'Terminal Petikemas Surabaya (TPS)',
            'billing_issuance_status' => BillingIssuanceStatus::Issued,
            'confirmation_checklist' => true,
            'billing_response' => BillingResponse::Spjk,
        ], [
            'current_milestone' => ImportMilestone::ContainerShippingSchedule,
        ]);

        $this->hsCodes($spjk, '8445.20');

        $this->container($spjk, 'CMAU9107007', '40', [
            'description_of_goods' => 'Textile machinery',
            'packages' => '55 crates',
            'status' => ContainerStatus::InProgress,
        ]);
    }

    /**
     * A cancelled shipment, so the cancelled status badge appears on an import
     * record too.
     */
    private function seedCancelledShipment(): void
    {
        $this->shipment('JRD', [
            'bl_number' => 'BL-IMP-0014',
            'shipment_mode' => ShipmentMode::Fcl,
            'shipping_line' => 'SITC',
            'vessel_name' => 'MV SITC Haiphong',
        ], [
            'status' => ShipmentStatus::Cancelled,
            'current_milestone' => ImportMilestone::WaitingConfirmation,
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
     * Containers keep whatever progress they have: the size is refreshed,
     * while the cargo fields and `$initial` progress fields only apply on
     * creation.
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
