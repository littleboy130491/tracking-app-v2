<?php

/**
 * File: app/Services/ShipmentTimelineEntry.php
 * Responsibility: Contract for one customer-visible journey row.
 * What it does:
 * - Holds the display fields the portal timeline needs (title, time, place).
 * - Flags actual vs estimate and the latest row, like the reference legend.
 * How to use: built by the timeline service from ActivityLog + shipment dates.
 * How to extend: add fields here first (e.g. vessel link), then fill them in the builder.
 */

namespace App\Services;

readonly class ShipmentTimelineEntry
{
    public function __construct(
        public string $title,
        public ?string $occurredAt = null,
        public ?string $location = null,
        public ?string $detail = null,
        public bool $isActual = true,
        public bool $isLatest = false,
        public string $sourceEvent = '',
    ) {}
}
