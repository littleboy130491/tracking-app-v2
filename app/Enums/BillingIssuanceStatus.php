<?php

/**
 * File: app/Enums/BillingIssuanceStatus.php
 * Responsibility: Whether the billing document has been issued.
 * What it does:
 * - Tracks not_issued / issued for a bill of lading.
 * How to use: BillOfLading casts `billing_issuance_status` to this enum.
 * How to extend: Add "cancelled" if billing can be voided.
 */

namespace App\Enums;

use App\Enums\Concerns\HasSelectOptions;

enum BillingIssuanceStatus: string
{
    use HasSelectOptions;

    case NotIssued = 'not_issued';
    case Issued = 'issued';

    public function label(): string
    {
        return match ($this) {
            self::NotIssued => 'Not Issued',
            self::Issued => 'Issued',
        };
    }
}
