<?php

/**
 * File: database/seeders/DemoActivityLogSeeder.php
 * Responsibility: Seeds activity-log and note rows for the demo records.
 * What it does:
 * - Writes one milestone_changed log per reached transition (steps 2..n —
 *   the initial step is never logged) on every demo shipment, a day apart
 *   ending yesterday, so Tracking progress shows real datetimes.
 * - Aligns the seeded dates to that trail: document_received_date takes the
 *   first step's date, a completed shipment's completed_at takes the last
 *   step's time, and filled container timestamps move to their step's time.
 * - Adds a couple of free-text notes on a demo shipment.
 * - Looks shipments up via the models, so it depends on the shipment seeders.
 * - Idempotent: a marker value keeps re-runs from writing the trail twice.
 * How to use: run by DatabaseSeeder after the shipment seeders.
 * How to extend: new demo shipments are picked up automatically.
 */

namespace Database\Seeders;

use App\Enums\ExportMilestone;
use App\Enums\ImportMilestone;
use App\Enums\ShipmentStatus;
use App\Models\ExportContainer;
use App\Models\ExportShipment;
use App\Models\ImportContainer;
use App\Models\ImportShipment;
use App\Models\User;
use App\Services\ActivityLogger;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DemoActivityLogSeeder extends Seeder
{
    /** Marker stored in new_values so re-runs can skip an already-seeded trail. */
    private const MARKER = 'seeded_demo_trail';

    public function run(): void
    {
        $actor = User::query()->where('email', 'admin@example.com')->first();

        $this->seedMilestoneTrails(app(ActivityLogger::class), $actor);
        $this->seedNotes();
    }

    /**
     * One milestone_changed log per reached transition on every seeded
     * shipment, a day apart ending yesterday, so Tracking progress shows
     * datetimes. Real usage never logs the first step — moveToMilestone()
     * only records transitions — so the trail starts at step 2 and the
     * marker rides on that first log. Drafts, cancelled shipments, anything
     * still on step 1 and anything already carrying a trail are left alone.
     */
    private function seedMilestoneTrails(ActivityLogger $logger, ?User $actor): void
    {
        $shipments = ExportShipment::query()->get()
            ->concat(ImportShipment::query()->get());

        foreach ($shipments as $shipment) {
            if ($shipment->status === ShipmentStatus::Draft
                || $shipment->status === ShipmentStatus::Cancelled
                || $shipment->milestonePosition() < 2
                || $this->alreadySeeded($shipment)
                || $shipment->activityLogs()->where('event', 'milestone_changed')->exists()
            ) {
                continue;
            }

            $sequence = $shipment->milestoneSequence();
            $reached = array_slice($sequence, 0, $shipment->milestonePosition());

            // A time per reached step, including the unlogged first step —
            // its date is the document's received date.
            $stepTimes = [];
            $at = today()->subDays(count($reached))->setTime(9, 15);

            foreach ($reached as $milestone) {
                $stepTimes[$milestone->value] = $at;
                $at = $at->copy()->addDay();
            }

            $previous = $reached[0]->value;

            foreach (array_slice($reached, 1) as $index => $milestone) {
                $label = $milestone instanceof HasLabel ? $milestone->getLabel() : (string) $milestone->value;

                $logger->record(
                    shipment: $shipment,
                    event: 'milestone_changed',
                    entityType: $shipment::class,
                    entityId: $shipment->getKey(),
                    oldValues: ['milestone' => $previous],
                    newValues: ['milestone' => $milestone->value, ...($index === 0 ? [self::MARKER => true] : [])],
                    customerSummary: 'Progress moved to '.$label.'.',
                    actor: $actor,
                    occurredAt: $stepTimes[$milestone->value],
                );

                $previous = $milestone->value;
            }

            $this->alignSeededDates($shipment, $stepTimes);
        }
    }

    /**
     * Moves the seeded dates onto the trail so one event shows one date: the
     * document received date is the trail's first step, a completed shipment
     * finishes when its last step was reached, and a filled container
     * timestamp lands on the step that produced it. Values are only moved
     * when they exist — nothing is invented — via saveQuietly() so the
     * alignment itself writes no audit rows.
     *
     * @param  array<string, Carbon>  $stepTimes  reached milestone value => time
     */
    private function alignSeededDates(ExportShipment|ImportShipment $shipment, array $stepTimes): void
    {
        // The column is a `date`: write the date part only, or the stored
        // "Y-m-d H:i:s" makes the field look dirty on the next admin save.
        $updates = ['document_received_date' => reset($stepTimes)->toDateString()];

        if ($shipment->status === ShipmentStatus::Completed) {
            $updates['completed_at'] = end($stepTimes);
        }

        $shipment->forceFill($updates)->saveQuietly();

        foreach ($shipment->containers as $container) {
            $updates = [];

            if ($container instanceof ImportContainer) {
                if (filled($container->gate_out_cy_at) && isset($stepTimes[ImportMilestone::GateOutCy->value])) {
                    $updates['gate_out_cy_at'] = $stepTimes[ImportMilestone::GateOutCy->value];
                }

                if (isset($stepTimes[ImportMilestone::EmptyReturned->value])) {
                    if (filled($container->empty_returned_at)) {
                        $updates['empty_returned_at'] = $stepTimes[ImportMilestone::EmptyReturned->value];
                    }

                    if (filled($container->completed_at)) {
                        $updates['completed_at'] = $stepTimes[ImportMilestone::EmptyReturned->value];
                    }
                }
            }

            if ($container instanceof ExportContainer) {
                if (filled($container->gate_in_cy_at) && isset($stepTimes[ExportMilestone::GateInCy->value])) {
                    $updates['gate_in_cy_at'] = $stepTimes[ExportMilestone::GateInCy->value];
                }

                if (filled($container->final_checked_at) && isset($stepTimes[ExportMilestone::FinalChecking->value])) {
                    $updates['final_checked_at'] = $stepTimes[ExportMilestone::FinalChecking->value];
                }
            }

            if ($updates !== []) {
                $container->forceFill($updates)->saveQuietly();
            }
        }
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
