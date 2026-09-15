<?php

/**
 * File: app/Filament/Resources/Containers/Pages/CreateContainer.php
 * Responsibility: Creates a container.
 * What it does:
 * - Renders the container form and keeps the create action in the page
 *   header. Containers are usually added via the B/L form's repeater.
 * How to use: Reached from the Container resource "Create" button.
 * How to extend: Add post-create side effects in afterCreate().
 */

namespace App\Filament\Resources\Containers\Pages;

use App\Filament\Resources\Containers\ContainerResource;
use Filament\Resources\Pages\CreateRecord;

class CreateContainer extends CreateRecord
{
    protected static string $resource = ContainerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->getCreateFormAction(),
        ];
    }
}
