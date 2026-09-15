<?php

/**
 * File: app/Filament/Resources/BillOfLadings/Pages/EditBillOfLading.php
 * Responsibility: Edits a bill of lading.
 * What it does:
 * - Renders the document/event/containers form; header exposes restore
 *   (on trashed records) and save.
 * How to use: Reached by editing a shipment.
 * How to extend: Add header actions for shipment-level operations.
 */

namespace App\Filament\Resources\BillOfLadings\Pages;

use App\Filament\Resources\BillOfLadings\BillOfLadingResource;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditBillOfLading extends EditRecord
{
    protected static string $resource = BillOfLadingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            RestoreAction::make(),
            $this->getSaveFormAction(),
        ];
    }
}
