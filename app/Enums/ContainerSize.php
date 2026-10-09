<?php

/**
 * File: app/Enums/ContainerSize.php
 * Responsibility: The container size/type options used on shipment containers.
 * What it does:
 * - Backs the `size` column on both container tables; values match stored data.
 * - Feeds the Container Size selects and display labels via HasSelectOptions/label().
 * How to use: `ContainerSize::options()` in selects; models cast `size` to this enum.
 * How to extend: Add a case plus its label here — forms and displays pick it up.
 */

namespace App\Enums;

use App\Enums\Concerns\HasSelectOptions;

enum ContainerSize: string
{
    use HasSelectOptions;

    case Ft20 = '20';
    case Ft40 = '40';
    case Ft45 = '45';
    case Lcl = 'lcl';
    case Ot20 = '20_ot';
    case Ot40 = '40_ot';
    case Flat20 = '20_flat';
    case Flat40 = '40_flat';
    case Fl20 = '20_fl';
    case Ho40 = '40_ho';
    case Dv20 = '20_dv';
    case Hc40 = '40_hc';
    case BreakBulk = 'break_bulk';
    case Tl = 'tl';

    public function label(): string
    {
        return match ($this) {
            self::Ft20 => '20 ft',
            self::Ft40 => '40 ft',
            self::Ft45 => '45 ft',
            self::Lcl => 'LCL',
            self::Ot20 => '20 OT',
            self::Ot40 => '40 OT',
            self::Flat20 => '20 FLAT',
            self::Flat40 => '40 FLAT',
            self::Fl20 => '20 FL',
            self::Ho40 => '40 HO',
            self::Dv20 => '20 DV',
            self::Hc40 => '40 HC',
            self::BreakBulk => 'BREAK BULK',
            self::Tl => 'TL',
        };
    }
}
