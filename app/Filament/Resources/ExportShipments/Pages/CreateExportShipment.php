<?php

/**
 * File: app/Filament/Resources/ExportShipments/Pages/CreateExportShipment.php
 * Responsibility: Creates an export shipment.
 * What it does:
 * - Renders the customer form (only the customer section shows on create) and
 *   keeps the create action in the page header.
 * How to use: Reached from the Export Shipments resource "Create" button.
 * How to extend: Add post-create side effects in afterCreate().
 */

namespace App\Filament\Resources\ExportShipments\Pages;

use App\Filament\Resources\ExportShipments\ExportShipmentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateExportShipment extends CreateRecord
{
    protected static string $resource = ExportShipmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->getCreateFormAction(),
        ];
    }
}
