<?php

/**
 * File: app/Enums/StuffingStatus.php
 * Responsibility: Stuffing progress for a container.
 * What it does:
 * - Tracks not_started / on_process / finished as required by migration_plan.md.
 * How to use: Container casts `stuffing_status` to this enum.
 * How to extend: Add stages such as "cancelled" if the operation allows it.
 */

namespace App\Enums;

use App\Enums\Concerns\HasSelectOptions;

enum StuffingStatus: string
{
    use HasSelectOptions;

    case NotStarted = 'not_started';
    case OnProcess = 'on_process';
    case Finished = 'finished';

    public function label(): string
    {
        return match ($this) {
            self::NotStarted => 'Not Started',
            self::OnProcess => 'On Process',
            self::Finished => 'Finished',
        };
    }
}
