<?php

/**
 * File: app/Filament/Resources/ExportContainers/Pages/ListExportContainers.php
 * Responsibility: Lists export containers.
 * What it does:
 * - Renders the export containers table with the create action in the page header.
 * How to use: Reached from Export → Containers.
 * How to extend: Add header actions for list-level operations.
 */

namespace App\Filament\Resources\ExportContainers\Pages;

use App\Filament\Resources\ExportContainers\ExportContainerResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListExportContainers extends ListRecords
{
    protected static string $resource = ExportContainerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
