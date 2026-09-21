<?php

/**
 * File: app/Enums/FactoryLoadingStatus.php
 * Responsibility: Factory loading progress for an import container.
 * What it does:
 * - Tracks on_process / finished per IMPORT.md ("Loading in Factory Status");
 *   loading starts as soon as the container is at the factory, so there is no
 *   "not started" state.
 * How to use: Container casts `factory_loading_status` to this enum.
 * How to extend: Add stages such as "cancelled" if the operation allows it.
 */

namespace App\Enums;

use App\Enums\Concerns\HasSelectOptions;

enum FactoryLoadingStatus: string
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
