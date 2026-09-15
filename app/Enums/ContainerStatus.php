<?php

/**
 * File: app/Enums/ContainerStatus.php
 * Responsibility: Lifecycle status of a shipment-scoped container.
 * What it does:
 * - `completed` is set when the container is empty and returned to a depot.
 * How to use: Container casts `status` to this enum.
 * How to extend: Add states together with the shipment progress service.
 */

namespace App\Enums;

use App\Enums\Concerns\HasSelectOptions;

enum ContainerStatus: string
{
    use HasSelectOptions;

    case Pending = 'pending';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::InProgress => 'In Progress',
            self::Completed => 'Completed',
            self::Cancelled => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'gray',
            self::InProgress => 'warning',
            self::Completed => 'success',
            self::Cancelled => 'danger',
        };
    }
}
