<?php

/**
 * File: app/Filament/Resources/Containers/Pages/EditContainer.php
 * Responsibility: Edits a container.
 * What it does:
 * - Renders the container form; header exposes restore (on trashed
 *   records) and save.
 * How to use: Reached by editing a container.
 * How to extend: Add header actions for container-level operations.
 */

namespace App\Filament\Resources\Containers\Pages;

use App\Filament\Resources\Containers\ContainerResource;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditContainer extends EditRecord
{
    protected static string $resource = ContainerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            RestoreAction::make(),
            $this->getSaveFormAction(),
        ];
    }
}
