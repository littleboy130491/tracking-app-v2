<?php

/**
 * File: app/Enums/BillingResponse.php
 * Responsibility: The response code returned by the billing/behandle source.
 * What it does:
 * - SPPB, AP, SPJK and SPJM as listed in migration_plan.md §4 and §12.
 * - SPJM marks the behandle branch; SPPB is the follow-up state after SPJM.
 * How to use: ImportShipment casts `billing_response` to this enum (nullable).
 * How to extend: Add new response codes as cases plus a label.
 */

namespace App\Enums;

use App\Enums\Concerns\HasSelectOptions;

enum BillingResponse: string
{
    use HasSelectOptions;

    case Sppb = 'SPPB';
    case Ap = 'AP';
    case Spjk = 'SPJK';
    case Spjm = 'SPJM';

    public function label(): string
    {
        return $this->value;
    }

    public function color(): string
    {
        return match ($this) {
            self::Sppb => 'success',
            self::Ap, self::Spjk => 'warning',
            self::Spjm => 'danger',
        };
    }
}
