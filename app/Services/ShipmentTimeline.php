<?php

/**
 * File: app/Services/ShipmentTimeline.php
 * Responsibility: Builds the customer-visible journey for a shipment or container.
 * What it does:
 * - Merges customer-visible activity logs with the shipment's own dated fields
 *   (pickup, stuffing, gate in/out, sailing dates, empty return) into one
 *   chronological list of ShipmentTimelineEntry, flagging the last as latest.
 * - ETA is marked as an estimate; everything else is marked actual.
 * How to use: `app(ShipmentTimeline::class)->forContainer($container)` in the portal.
 * How to extend: add a dated field as one more `dated()` candidate below.
 */

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\ExportContainer;
use App\Models\ExportShipment;
use App\Models\ImportContainer;
use App\Models\ImportShipment;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;

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

        $stuffingDestination = $shipment instanceof ExportShipment ? $shipment->stuffing_destination : null;
        $this->pushContainerDates($candidates, $container, $stuffingDestination);

        if ($shipment) {
            $this->pushVoyageDates($candidates, $shipment);
            $this->pushVisibleLogs($candidates, $shipment, $container);
        }

        return $this->toEntries($candidates);
    }

    /**
     * The shipment-level journey: voyage dates plus shipment-wide visible logs.
     *
     * @return list<ShipmentTimelineEntry>
     */
    public function forShipment(ExportShipment|ImportShipment $shipment): array
    {
        $candidates = [];

        $this->pushVoyageDates($candidates, $shipment);
        $this->pushVisibleLogs($candidates, $shipment, null);

        return $this->toEntries($candidates);
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
     * The shipment's current position, for list rows like the reference's
     * Latest Place / Latest Event columns.
     */
    public function latestForShipment(ExportShipment|ImportShipment $shipment): ?ShipmentTimelineEntry
    {
        $entries = $this->forShipment($shipment);

        return $entries === [] ? null : end($entries);
    }

    /**
     * Container operational dates, per process. Each maps to one
     * reference-style event row; stuffing destination lives on the export
     * shipment header and is passed in.
     *
     * @param  list<array{at: CarbonInterface, title: string, location: ?string, detail: ?string, actual: bool, source: string}>  $candidates
     */
    private function pushContainerDates(
        array &$candidates,
        ExportContainer|ImportContainer $container,
        ?string $stuffingDestination,
    ): void {
        if ($container instanceof ExportContainer) {
            $this->dated($candidates, $container->stuffing_started_at, 'Stuffing started', $stuffingDestination, 'container:stuffing_started_at');
            $this->dated($candidates, $container->stuffing_finished_at, 'Stuffing finished', $stuffingDestination, 'container:stuffing_finished_at');
            $this->dated($candidates, $container->gate_in_cy_at, 'Gate in to terminal', $container->gate_in_port_name, 'container:gate_in_cy_at');
            $this->dated($candidates, $container->final_checked_at, 'Final checking completed', null, 'container:final_checked_at');

            return;
        }

        $this->dated($candidates, $container->gate_out_cy_at, 'Gate out from terminal for delivery', null, 'container:gate_out_cy_at');
        $this->dated($candidates, $container->inspected_at, 'Container inspected', null, 'container:inspected_at');
        $this->dated($candidates, $container->factory_arrived_at, 'Arrived at factory', null, 'container:factory_arrived_at');
        $this->dated($candidates, $container->empty_returned_at, 'Empty container returned', $container->return_depot_name, 'container:empty_returned_at');
    }

    /**
     * Shared sailing dates from the shipment header: actuals plus the ETA
     * estimate.
     *
     * @param  list<array{at: CarbonInterface, title: string, location: ?string, detail: ?string, actual: bool, source: string}>  $candidates
     */
    private function pushVoyageDates(array &$candidates, ExportShipment|ImportShipment $shipment): void
    {
        $vessel = trim(implode(' ', array_filter([$shipment->vessel_name, $shipment->voyage_number])));

        $this->dated($candidates, $shipment->departure_date, 'Vessel departure from port of loading', $shipment->port_of_loading, 'shipment:departure_date', $vessel ?: null);
        $this->dated($candidates, $shipment->actual_arrival_at, 'Vessel arrival at port of discharge', $shipment->port_of_discharge, 'shipment:actual_arrival_at', $vessel ?: null);

        if ($shipment->eta_at && ! $shipment->actual_arrival_at) {
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
