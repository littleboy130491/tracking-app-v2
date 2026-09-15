<?php

/**
 * File: app/Enums/ShipmentType.php
 * Responsibility: Identifies whether a shipment is an export or an import.
 * What it does:
 * - Drives which form sections a B/L and its containers show.
 * How to use: BillOfLading casts `shipment_type` to this enum.
 * How to extend: Add further types (e.g. domestic) and their labels.
 */

namespace App\Enums;

use App\Enums\Concerns\HasSelectOptions;

enum ShipmentType: string
{
    use HasSelectOptions;

    case Export = 'export';
    case Import = 'import';

    public function label(): string
    {
        return match ($this) {
            self::Export => 'Export',
            self::Import => 'Import',
        };
    }
}
