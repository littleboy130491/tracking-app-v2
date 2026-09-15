<?php

/**
 * File: app/Enums/DraftPibConfirmationStatus.php
 * Responsibility: Customer confirmation state for an import draft PIB.
 * What it does:
 * - The customer dashboard sets `confirmed` or `revision_requested` for imports.
 * How to use: BillOfLading casts `draft_pib_confirmation_status` to this enum.
 * How to extend: Add states together with the portal confirmation UI.
 */

namespace App\Enums;

use App\Enums\Concerns\HasSelectOptions;

enum DraftPibConfirmationStatus: string
{
    use HasSelectOptions;

    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case RevisionRequested = 'revision_requested';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Confirmed => 'Confirmed',
            self::RevisionRequested => 'Revision Requested',
        };
    }
}
