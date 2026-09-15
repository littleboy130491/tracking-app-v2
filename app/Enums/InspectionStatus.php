<?php

/**
 * File: app/Enums/InspectionStatus.php
 * Responsibility: Inspection progress for a container.
 * What it does:
 * - Tracks not_started / in_progress / completed as migration_plan.md requires.
 * How to use: Container casts `inspection_status` to this enum.
 * How to extend: Add result states (passed/failed) as separate columns instead.
 */

namespace App\Enums;

use App\Enums\Concerns\HasSelectOptions;

enum InspectionStatus: string
{
    use HasSelectOptions;

    case NotStarted = 'not_started';
    case InProgress = 'in_progress';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::NotStarted => 'Not Started',
            self::InProgress => 'In Progress',
            self::Completed => 'Completed',
        };
    }
}
