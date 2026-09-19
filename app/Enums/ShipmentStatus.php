<?php

/**
 * File: app/Enums/ShipmentStatus.php
 * Responsibility: Lifecycle status of a shipment (export or import).
 * What it does:
 * - `draft` allows saving with incomplete data; `completed` marks the whole
 *   shipment finished.
 * How to use: ExportShipment and ImportShipment cast `status` to this enum.
 * How to extend: Add states only together with the service that transitions them.
 */

namespace App\Enums;

use App\Enums\Concerns\HasSelectOptions;

enum ShipmentStatus: string
{
    use HasSelectOptions;

    case Draft = 'draft';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::InProgress => 'In Progress',
            self::Completed => 'Completed',
            self::Cancelled => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::InProgress => 'warning',
            self::Completed => 'success',
            self::Cancelled => 'danger',
        };
    }
}
