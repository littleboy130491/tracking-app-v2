<?php

/**
 * File: app/Enums/StuffingStatus.php
 * Responsibility: Stuffing progress for a container.
 * What it does:
 * - Tracks on_process / finished; stuffing starts as soon as the container is
 *   being loaded, so there is no "not started" state.
 * How to use: Container casts `stuffing_status` to this enum.
 * How to extend: Add stages such as "cancelled" if the operation allows it.
 */

namespace App\Enums;

use App\Enums\Concerns\HasSelectOptions;

enum StuffingStatus: string
{
    use HasSelectOptions;

    case OnProcess = 'on_process';
    case Finished = 'finished';

    public function label(): string
    {
        return match ($this) {
            self::OnProcess => 'On Process',
            self::Finished => 'Finished',
        };
    }
}
