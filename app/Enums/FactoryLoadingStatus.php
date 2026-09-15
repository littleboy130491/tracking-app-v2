<?php

/**
 * File: app/Enums/FactoryLoadingStatus.php
 * Responsibility: Factory loading progress for a container.
 * What it does:
 * - Tracks not_started / on_process / final_process per migration_plan.md.
 * How to use: Container casts `factory_loading_status` to this enum.
 * How to extend: Add "cancelled" if loading can be aborted mid-way.
 */

namespace App\Enums;

use App\Enums\Concerns\HasSelectOptions;

enum FactoryLoadingStatus: string
{
    use HasSelectOptions;

    case NotStarted = 'not_started';
    case OnProcess = 'on_process';
    case FinalProcess = 'final_process';

    public function label(): string
    {
        return match ($this) {
            self::NotStarted => 'Not Started',
            self::OnProcess => 'On Process',
            self::FinalProcess => 'Final Process',
        };
    }
}
