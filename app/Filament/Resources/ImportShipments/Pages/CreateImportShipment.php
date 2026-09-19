<?php

/**
 * File: app/Filament/Resources/ImportShipments/Pages/CreateImportShipment.php
 * Responsibility: Creates an import shipment.
 * What it does:
 * - Renders the customer form (only the customer section shows on create) and
 *   keeps the create action in the page header.
 * How to use: Reached from the Import Shipments resource "Create" button.
 * How to extend: Add post-create side effects in afterCreate().
 */

namespace App\Filament\Resources\ImportShipments\Pages;

use App\Filament\Resources\ImportShipments\ImportShipmentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateImportShipment extends CreateRecord
{
    protected static string $resource = ImportShipmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->getCreateFormAction(),
        ];
    }
}
