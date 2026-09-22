<?php

/**
 * File: database/seeders/DemoActivityLogSeeder.php
 * Responsibility: Seeds activity-log and note rows for the demo records.
 * What it does:
 * - Writes a small, realistic audit trail (shipment created/updated, HS codes,
 *   container added, milestone changes) on a few shipments, mixed between
 *   customer-visible and internal, plus a couple of notes.
 * - Goes through ActivityLogger, so the denormalized latest-event columns stay
 *   consistent with the rows.
 * - Looks shipments up by B/L number, so it depends on the shipment seeders.
 * - Idempotent: a marker value keeps re-runs from writing the trail twice.
 * How to use: run by DatabaseSeeder after the shipment seeders.
 * How to extend: add another B/L number to the map.
 */

namespace Database\Seeders;

use App\Enums\ImportMilestone;
use App\Models\ExportShipment;
use App\Models\ImportShipment;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Database\Seeder;

class DemoActivityLogSeeder extends Seeder
{
    /** Marker stored in new_values so re-runs can skip an already-seeded trail. */
    private const MARKER = 'seeded_demo_trail';

    public function run(): void
    {
        $logger = app(ActivityLogger::class);
        $actor = User::query()->where('email', 'admin@example.com')->first();
        $today = today()->setTime(9, 15);

        $this->seedExportTrail($logger, 'BL-EXP-0006', $actor, $today);
        $this->seedImportTrail($logger, 'BL-IMP-0010', $actor, $today);
        $this->seedNotes();
    }

    /**
     * A completed export shipment's journey, so the audit tab and monitoring
     * list show a full trail.
     */
    private function seedExportTrail(ActivityLogger $logger, string $bl, ?User $actor, \DateTimeInterface $at): void
    {
        $shipment = ExportShipment::query()->where('bl_number', $bl)->first();

        if ($shipment === null || $this->alreadySeeded($shipment)) {
            return;
        }

        $logger->record(
            shipment: $shipment,
            event: 'shipment_created',
            entityType: $shipment::class,
            entityId: $shipment->getKey(),
            newValues: ['bl_number' => $shipment->bl_number, self::MARKER => true],
            customerSummary: 'Shipment created.',
            customerVisible: true,
            actor: $actor,
        );

        $logger->record(
            shipment: $shipment,
            event: 'milestone_changed',
            entityType: $shipment::class,
            entityId: $shipment->getKey(),
            oldValues: ['milestone' => 'gate_in_cy'],
            newValues: ['milestone' => 'final_checking'],
            customerSummary: 'Progress moved to Final checking.',
            customerVisible: true,
            actor: $actor,
        );

        $container = $shipment->containers()->first();

        if ($container !== null) {
            $logger->record(
                shipment: $shipment,
                event: 'container_created',
                entityType: $container::class,
                entityId: $container->getKey(),
                container: $container,
                newValues: ['container_number' => $container->container_number],
                customerSummary: 'Container '.$container->container_number.' added.',
                customerVisible: true,
                actor: $actor,
            );
        }
    }

    /**
     * A completed import shipment's journey on the SPJM→SPPB branch.
     */
    private function seedImportTrail(ActivityLogger $logger, string $bl, ?User $actor, \DateTimeInterface $at): void
    {
        $shipment = ImportShipment::query()->where('bl_number', $bl)->first();

        if ($shipment === null || $this->alreadySeeded($shipment)) {
            return;
        }

        $logger->record(
            shipment: $shipment,
            event: 'shipment_created',
            entityType: $shipment::class,
            entityId: $shipment->getKey(),
            newValues: ['bl_number' => $shipment->bl_number, self::MARKER => true],
            customerSummary: 'Shipment created.',
            customerVisible: true,
            actor: $actor,
        );

        $logger->record(
            shipment: $shipment,
            event: 'shipment_updated',
            entityType: $shipment::class,
            entityId: $shipment->getKey(),
            oldValues: ['billing_response' => 'SPJM'],
            newValues: ['billing_response' => 'SPPB'],
            customerSummary: 'Billing response settled on SPPB.',
            customerVisible: true,
            actor: $actor,
        );

        $logger->record(
            shipment: $shipment,
            event: 'milestone_changed',
            entityType: $shipment::class,
            entityId: $shipment->getKey(),
            oldValues: ['milestone' => ImportMilestone::ContainerShippingSchedule->value],
            newValues: ['milestone' => ImportMilestone::EmptyReturned->value],
            customerSummary: 'Progress moved to '.ImportMilestone::EmptyReturned->getLabel().'.',
            customerVisible: true,
            actor: $actor,
        );

        // One internal-only entry, to prove the portal filters it out.
        $logger->record(
            shipment: $shipment,
            event: 'shipment_updated',
            entityType: $shipment::class,
            entityId: $shipment->getKey(),
            oldValues: null,
            newValues: ['internal_note' => 'Rate re-checked against carrier tariff'],
            customerSummary: null,
            customerVisible: false,
            actor: $actor,
        );
    }

    /**
     * A couple of free-text notes on demo records.
     */
    private function seedNotes(): void
    {
        $actor = User::query()->where('email', 'admin@example.com')->first();

        $shipment = ImportShipment::query()->where('bl_number', 'BL-IMP-0010')->first();

        if ($shipment === null) {
            return;
        }

        foreach ([
            'Customer asked to prioritise the DO release this week.',
            'Warehouse ready from Monday; confirm delivery slot.',
        ] as $index => $body) {
            $shipment->notes()->firstOrCreate(
                ['body' => $body],
                ['author_id' => $actor?->getKey()],
            );
        }
    }

    /**
     * Whether this shipment already carries the seeded demo trail.
     */
    private function alreadySeeded(ExportShipment|ImportShipment $shipment): bool
    {
        return $shipment->activityLogs()
            ->where('new_values->'.self::MARKER, true)
            ->exists();
    }
}
