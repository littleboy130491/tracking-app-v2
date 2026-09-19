<?php

/**
 * File: app/Filament/Resources/ExportShipments/Pages/ListExportShipments.php
 * Responsibility: Lists export shipments.
 * What it does:
 * - Renders the export shipments table with the create action in the page header.
 * How to use: Reached from Export → Shipments.
 * How to extend: Add header actions for list-level operations.
 */

namespace App\Filament\Resources\ExportShipments\Pages;

use App\Filament\Resources\ExportShipments\ExportShipmentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListExportShipments extends ListRecords
{
    protected static string $resource = ExportShipmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
