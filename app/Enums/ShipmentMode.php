<?php

/**
 * File: app/Enums/ShipmentMode.php
 * Responsibility: Identifies how a shipment's cargo is loaded and moved.
 * What it does:
 * - FCL / LCL split sea freight by container use; Air covers air shipments.
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

    public function label(): string
    {
        return match ($this) {
            self::Fcl => 'FCL',
            self::Lcl => 'LCL',
            self::Air => 'Air Shipment',
        };
    }
}
