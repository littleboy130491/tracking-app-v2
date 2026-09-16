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
use App\Models\BillOfLading;
use App\Models\Container;
use Carbon\CarbonInterface;

class ShipmentTimeline
{
    /**
     * One container's full journey, oldest first. Shared voyage dates from the
     * parent B/L are included so a container with no logs yet still shows
     * its sailing context.
     *
     * @return list<ShipmentTimelineEntry>
     */
    public function forContainer(Container $container): array
    {
        $container->loadMissing('billOfLading');

        $billOfLading = $container->billOfLading;
        $candidates = [];

        $this->pushContainerDates($candidates, $container);

        if ($billOfLading) {
            $this->pushVoyageDates($candidates, $billOfLading);
            $this->pushVisibleLogs($candidates, $billOfLading, $container->getKey());
        }

        return $this->toEntries($candidates);
    }

    /**
     * The shipment-level journey: voyage dates plus B/L-wide visible logs.
     *
     * @return list<ShipmentTimelineEntry>
     */
    public function forBillOfLading(BillOfLading $billOfLading): array
    {
        $candidates = [];

        $this->pushVoyageDates($candidates, $billOfLading);
        $this->pushVisibleLogs($candidates, $billOfLading, null);

        return $this->toEntries($candidates);
    }

    /**
     * The container's current position in its journey, if anything is known.
     */
    public function latestForContainer(Container $container): ?ShipmentTimelineEntry
    {
        $entries = $this->forContainer($container);

        return $entries === [] ? null : end($entries);
    }

    /**
     * The shipment's current position, for list rows like the reference's
     * Latest Place / Latest Event columns.
     */
    public function latestForBillOfLading(BillOfLading $billOfLading): ?ShipmentTimelineEntry
    {
        $entries = $this->forBillOfLading($billOfLading);

        return $entries === [] ? null : end($entries);
    }

    /**
     * Container operational dates. Each maps to one reference-style event row.
     *
     * @param  list<array{at: CarbonInterface, title: string, location: ?string, detail: ?string, actual: bool, source: string}>  $candidates
     */
    private function pushContainerDates(array &$candidates, Container $container): void
    {
        $this->dated($candidates, $container->empty_picked_up_at, 'Empty container picked up', $container->pickup_depot_name, 'container:empty_picked_up_at');
        $this->dated($candidates, $container->stuffing_started_at, 'Stuffing started', $container->stuffing_destination, 'container:stuffing_started_at');
        $this->dated($candidates, $container->stuffing_finished_at, 'Stuffing finished', $container->stuffing_destination, 'container:stuffing_finished_at');
        $this->dated($candidates, $container->inspected_at, 'Container inspected', null, 'container:inspected_at');
        $this->dated($candidates, $container->factory_arrived_at, 'Arrived at factory', null, 'container:factory_arrived_at');
        $this->dated($candidates, $container->gate_in_cy_at, 'Gate in to terminal', $container->gate_in_port_name, 'container:gate_in_cy_at');
        $this->dated($candidates, $container->gate_out_cy_at, 'Gate out from terminal for delivery', null, 'container:gate_out_cy_at');
        $this->dated($candidates, $container->final_checked_at, 'Final checking completed', null, 'container:final_checked_at');
        $this->dated($candidates, $container->empty_returned_at, 'Empty container returned', $container->return_depot_name, 'container:empty_returned_at');
    }

    /**
     * Shared sailing dates from the B/L header: actuals plus the ETA estimate.
     *
     * @param  list<array{at: CarbonInterface, title: string, location: ?string, detail: ?string, actual: bool, source: string}>  $candidates
     */
    private function pushVoyageDates(array &$candidates, BillOfLading $billOfLading): void
    {
        $vessel = trim(implode(' ', array_filter([$billOfLading->vessel_name, $billOfLading->voyage_number])));

        $this->dated($candidates, $billOfLading->departure_date, 'Vessel departure from port of loading', $billOfLading->port_of_loading, 'bill_of_lading:departure_date', $vessel ?: null);
        $this->dated($candidates, $billOfLading->actual_arrival_at, 'Vessel arrival at port of discharge', $billOfLading->port_of_discharge, 'bill_of_lading:actual_arrival_at', $vessel ?: null);

        if ($billOfLading->eta_at && ! $billOfLading->actual_arrival_at) {
            $candidates[] = [
                'at' => $billOfLading->eta_at,
                'title' => 'Vessel arrival at port of discharge (estimate)',
                'location' => $billOfLading->port_of_discharge,
                'detail' => $vessel ?: null,
                'actual' => false,
                'source' => 'bill_of_lading:eta_at',
            ];
        }
    }

    /**
     * Customer-visible log rows. For a container this means B/L-wide entries
     * (milestones, PIB confirmation) plus that container's own entries; other
     * containers' entries stay out. For a B/L only B/L-wide entries qualify.
     *
     * @param  list<array{at: CarbonInterface, title: string, location: ?string, detail: ?string, actual: bool, source: string}>  $candidates
     */
    private function pushVisibleLogs(array &$candidates, BillOfLading $billOfLading, ?int $containerId): void
    {
        $logs = ActivityLog::query()
            ->where('bill_of_lading_id', $billOfLading->getKey())
            ->where('is_customer_visible', true)
            ->when(
                $containerId === null,
                fn ($query) => $query->whereNull('container_id'),
                fn ($query) => $query->where(fn ($inner) => $inner->whereNull('container_id')->orWhere('container_id', $containerId)),
            )
            ->orderBy('occurred_at')
            ->get();

        foreach ($logs as $log) {
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
