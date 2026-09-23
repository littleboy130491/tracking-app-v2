<?php

/**
 * File: app/Services/ShipmentTimelineEntry.php
 * Responsibility: Contract for one customer-visible progress row.
 * What it does:
 * - Holds the display fields the portal progress list needs (title, time and
 *   optional milestone-specific shipment values); future steps are pending.
 * - Flags the latest row for the timeline lists.
 * How to use: built by the timeline service from milestone logs + shipment dates.
 * How to extend: add another typed display property here, then fill it in the timeline builder.
 */

namespace App\Services;

readonly class ShipmentTimelineEntry
{
    /** @param list<array{label: string, value: string, href?: string, wide?: bool}> $fields */
    public function __construct(
        public string $title,
        public ?string $occurredAt = null,
        public bool $isLatest = false,
        public bool $isPending = false,
        public array $fields = [],
    ) {}
}
