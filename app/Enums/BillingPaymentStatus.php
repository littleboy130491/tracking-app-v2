<?php

/**
 * File: app/Enums/BillingPaymentStatus.php
 * Responsibility: Payment progress shared by billing, THC and behandle flows.
 * What it does:
 * - Tracks not_paid / processing / paid.
 * How to use: BillOfLading casts the three payment status columns to this enum.
 * How to extend: Add "refunded" or "failed" only with a matching transition rule.
 */

namespace App\Enums;

use App\Enums\Concerns\HasSelectOptions;

enum BillingPaymentStatus: string
{
    use HasSelectOptions;

    case NotPaid = 'not_paid';
    case Processing = 'processing';
    case Paid = 'paid';

    public function label(): string
    {
        return match ($this) {
            self::NotPaid => 'Not Paid',
            self::Processing => 'Processing',
            self::Paid => 'Paid',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::NotPaid => 'danger',
            self::Processing => 'warning',
            self::Paid => 'success',
        };
    }
}
