<?php

/**
 * File: app/Filament/Resources/ImportShipments/Pages/ListImportShipments.php
 * Responsibility: Lists import shipments.
 * What it does:
 * - Renders the import shipments table with the create action in the page header.
 * How to use: Reached from Import → Shipments.
 * How to extend: Add header actions for list-level operations.
 */

namespace App\Filament\Resources\ImportShipments\Pages;

use App\Filament\Resources\ImportShipments\ImportShipmentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListImportShipments extends ListRecords
{
    protected static string $resource = ImportShipmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
