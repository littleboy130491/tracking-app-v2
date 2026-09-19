<?php

/**
 * File: app/Filament/Resources/ImportContainers/Pages/ListImportContainers.php
 * Responsibility: Lists import containers.
 * What it does:
 * - Renders the import containers table with the create action in the page header.
 * How to use: Reached from Import → Containers.
 * How to extend: Add header actions for list-level operations.
 */

namespace App\Filament\Resources\ImportContainers\Pages;

use App\Filament\Resources\ImportContainers\ImportContainerResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListImportContainers extends ListRecords
{
    protected static string $resource = ImportContainerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
