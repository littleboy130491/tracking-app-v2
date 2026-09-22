<?php

/**
 * File: app/Services/ShipmentTimeline.php
 * Responsibility: Builds the customer-visible progress for a shipment or container.
 * What it does:
 * - Shipments show their milestone steps in order: reached steps carry the
 *   datetime from the milestone-change log trail (the first step falls back
 *   to the document date), the current step is flagged latest and upcoming
 *   steps are pending.
 * - Containers merge their own dated fields with voyage dates and visible
 *   logs into one chronological list, flagging the last as latest.
 * - ETA is marked as an estimate; everything else is marked actual.
 * How to use: `app(ShipmentTimeline::class)->forContainer($container)` in the portal.
 * How to extend: add a dated field as one more `dated()` candidate below.
 */

namespace App\Services;

use App\Enums\ExportMilestone;
use App\Enums\ImportMilestone;
use App\Models\ActivityLog;
use App\Models\ExportContainer;
use App\Models\ExportShipment;
use App\Models\ImportContainer;
use App\Models\ImportShipment;
use Carbon\CarbonInterface;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class ShipmentTimeline
{
    /**
     * One container's full journey, oldest first. Shared voyage dates from the
     * parent shipment are included so a container with no logs yet still shows
     * its sailing context.
     *
     * @return list<ShipmentTimelineEntry>
     */
    public function forContainer(ExportContainer|ImportContainer $container): array
    {
        $container->loadMissing('shipment');

        $shipment = $container->shipment;
        $candidates = [];

        $this->pushContainerDates($candidates, $container);

        if ($shipment) {
            $this->pushVoyageDates($candidates, $shipment);
            $this->pushVisibleLogs($candidates, $shipment, $container);
        }

        return $this->toEntries($candidates);
    }

    /**
     * The shipment-level progress: one row per milestone step, in order.
     * Reached steps carry the datetime they were logged at (the first step
     * falls back to the document date); upcoming steps are pending.
     *
     * @return list<ShipmentTimelineEntry>
     */
    public function forShipment(ExportShipment|ImportShipment $shipment): array
    {
        $sequence = $shipment->milestoneSequence();
        $position = $shipment->milestonePosition();

        // When each step was reached, from the milestone-change log trail.
        // A revisited step keeps its most recent pass.
        $reachedAt = ActivityLog::query()
            ->where($shipment->activityLogShipmentKey(), $shipment->getKey())
            ->where('event', 'milestone_changed')
            ->orderBy('occurred_at')
            ->get()
            ->mapWithKeys(fn (ActivityLog $log) => [
                (string) ($log->new_values['milestone'] ?? '') => $log->occurred_at,
            ]);

        $entries = [];

        foreach ($sequence as $index => $milestone) {
            $step = $index + 1;
            $pending = $step > $position;
            $at = $pending ? null : $reachedAt->get((string) $milestone->value);

            if ($at === null && ! $pending && $index === 0) {
                $at = $shipment->document_received_date ?? $shipment->created_at;
            }

            $entries[] = new ShipmentTimelineEntry(
                title: $milestone instanceof HasLabel ? $milestone->getLabel() : Str::headline((string) $milestone->value),
                occurredAt: $at ? $this->formatAt($at) : null,
                isActual: true,
                isLatest: ! $pending && $step === $position,
                isPending: $pending,
                sourceEvent: 'milestone:'.(string) $milestone->value,
            );
        }

        return $entries;
    }

    /**
     * Datetimes keep their time; bare dates (document date) show date only.
     */
    private function formatAt(CarbonInterface $at): string
    {
        return $at->format('H:i') === '00:00' ? $at->format('d M Y') : $at->format('d M Y H:i');
    }

    /**
     * The container's current position in its journey, if anything is known.
     */
    public function latestForContainer(ExportContainer|ImportContainer $container): ?ShipmentTimelineEntry
    {
        $entries = $this->forContainer($container);

        return $entries === [] ? null : end($entries);
    }

    /**
     * The shipment's current position: the latest reached step. Powers list
     * rows like the dashboard's Latest Place / Latest Event columns.
     */
    public function latestForShipment(ExportShipment|ImportShipment $shipment): ?ShipmentTimelineEntry
    {
        $reached = array_values(array_filter(
            $this->forShipment($shipment),
            fn (ShipmentTimelineEntry $entry) => ! $entry->isPending,
        ));

        return $reached === [] ? null : end($reached);
    }

    /**
     * Container operational dates, per process. Each maps to one
     * reference-style event row.
     *
     * @param  list<array{at: CarbonInterface, title: string, location: ?string, detail: ?string, actual: bool, source: string}>  $candidates
     */
    private function pushContainerDates(
        array &$candidates,
        ExportContainer|ImportContainer $container,
    ): void {
        if ($container instanceof ExportContainer) {
            $this->dated($candidates, $container->gate_in_cy_at, 'Gate in to terminal', $container->port_of_loading, 'container:gate_in_cy_at');
            $this->dated($candidates, $container->final_checked_at, 'Final checking completed', null, 'container:final_checked_at');

            return;
        }

        $this->dated($candidates, $container->gate_out_cy_at, 'Gate out from terminal for delivery', null, 'container:gate_out_cy_at');
        $this->dated($candidates, $container->empty_returned_at, 'Empty container returned', $container->return_depot_name, 'container:empty_returned_at');
    }

    /**
     * Shared sailing dates from the shipment header: actuals always, plus the
     * ETA estimate once its milestone is reached.
     *
     * @param  list<array{at: CarbonInterface, title: string, location: ?string, detail: ?string, actual: bool, source: string}>  $candidates
     */
    private function pushVoyageDates(array &$candidates, ExportShipment|ImportShipment $shipment): void
    {
        $vessel = trim(implode(' ', array_filter([$shipment->vessel_name, $shipment->voyage_number])));

        $this->dated($candidates, $shipment->departure_date, 'Vessel departure from port of loading', $shipment->port_of_loading, 'shipment:departure_date', $vessel ?: null);
        $this->dated($candidates, $shipment->actual_arrival_at, 'Vessel arrival at port of discharge', $shipment->port_of_discharge, 'shipment:actual_arrival_at', $vessel ?: null);

        if ($shipment->eta_at && ! $shipment->actual_arrival_at && $this->etaEstimateUnlocked($shipment)) {
            $candidates[] = [
                'at' => $shipment->eta_at,
                'title' => 'Vessel arrival at port of discharge (estimate)',
                'location' => $shipment->port_of_discharge,
                'detail' => $vessel ?: null,
                'actual' => false,
                'source' => 'shipment:eta_at',
            ];
        }
    }

    /**
     * Whether the ETA estimate may pose as progress: only once the milestone
     * that unlocks the ETA field is reached — Gate in CY for export (where the
     * vessel schedule is known), Payment billing for import.
     */
    private function etaEstimateUnlocked(ExportShipment|ImportShipment $shipment): bool
    {
        if ($shipment instanceof ExportShipment) {
            return ExportMilestone::unlocked(null, $shipment->current_milestone, ExportMilestone::GateInCy);
        }

        return ImportMilestone::unlocked($shipment->billing_response, $shipment->current_milestone, ImportMilestone::BillingPayment);
    }

    /**
     * Customer-visible log rows. For a container this means shipment-wide
     * entries (milestones, PIB confirmation) plus that container's own
     * entries; other containers' entries stay out. For a shipment only
     * shipment-wide entries qualify.
     *
     * @param  list<array{at: CarbonInterface, title: string, location: ?string, detail: ?string, actual: bool, source: string}>  $candidates
     */
    private function pushVisibleLogs(
        array &$candidates,
        ExportShipment|ImportShipment $shipment,
        ExportContainer|ImportContainer|null $container,
    ): void {
        $logs = ActivityLog::query()
            ->where($shipment->activityLogShipmentKey(), $shipment->getKey())
            ->where('is_customer_visible', true);

        if ($container === null) {
            $this->whereNoContainerLink($logs);
        } else {
            $logs->where(fn (Builder $inner) => $inner
                ->where(fn (Builder $none) => $this->whereNoContainerLink($none))
                ->orWhere($container->activityLogContainerKey(), $container->getKey()));
        }

        foreach ($logs->orderBy('occurred_at')->get() as $log) {
            $candidates[] = [
                'at' => $log->occurred_at,
                'title' => $log->customer_summary ?: $log->event,
                'location' => null,
                'detail' => null,
                'actual' => true,
                'source' => 'activity_log:'.$log->event,
            ];
        }
    }

    /**
     * Only shipment-wide entries: both container links are empty.
     */
    private function whereNoContainerLink(Builder $query): void
    {
        foreach (ActivityLog::CONTAINER_KEYS as $column) {
            $query->whereNull($column);
        }
    }

    /**
     * @param  list<array{at: CarbonInterface, title: string, location: ?string, detail: ?string, actual: bool, source: string}>  $candidates
     */
    private function dated(array &$candidates, ?CarbonInterface $at, string $title, ?string $location, string $source, ?string $detail = null): void
    {
        if (! $at) {
            return;
        }

        $candidates[] = [
            'at' => $at,
            'title' => $title,
            'location' => $location ?: null,
            'detail' => $detail,
            'actual' => true,
            'source' => $source,
        ];
    }

    /**
     * Oldest first, with the last row flagged as the latest.
     *
     * @param  list<array{at: CarbonInterface, title: string, location: ?string, detail: ?string, actual: bool, source: string}>  $candidates
     * @return list<ShipmentTimelineEntry>
     */
    private function toEntries(array $candidates): array
    {
        usort($candidates, fn (array $a, array $b) => $a['at']->timestamp <=> $b['at']->timestamp);

        $entries = [];
        $last = count($candidates) - 1;

        foreach ($candidates as $index => $candidate) {
            $entries[] = new ShipmentTimelineEntry(
                title: $candidate['title'],
                occurredAt: $candidate['at']->format('d M Y H:i'),
                location: $candidate['location'],
                detail: $candidate['detail'],
                isActual: $candidate['actual'],
                isLatest: $index === $last,
                sourceEvent: $candidate['source'],
            );
        }

        return $entries;
    }
}
