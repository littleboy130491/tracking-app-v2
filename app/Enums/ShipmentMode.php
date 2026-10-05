<?php

/**
 * File: app/Enums/ShipmentMode.php
 * Responsibility: Identifies how a shipment's cargo is loaded and moved.
 * What it does:
 * - FCL / LCL split sea freight by container use; Air covers air shipments;
 *   Break Bulk, FTL and OT add further cargo and truck modes.
 * How to use: ExportShipment and ImportShipment cast `shipment_mode` to this enum.
 * How to extend: Add further modes and their labels.
 */

namespace App\Enums;

use App\Enums\Concerns\HasSelectOptions;

enum ShipmentMode: string
{
    use HasSelectOptions;

    case Fcl = 'fcl';
    case Lcl = 'lcl';
    case Air = 'air';
    case BreakBulk = 'break_bulk';
    case Ftl = 'ftl';
    case Ot = 'ot';

    public function label(): string
    {
        return match ($this) {
            self::Fcl => 'FCL',
            self::Lcl => 'LCL',
            self::Air => 'Air Shipment',
            self::BreakBulk => 'Break Bulk',
            self::Ftl => 'FTL',
            self::Ot => 'OT',
        };
    }
}
