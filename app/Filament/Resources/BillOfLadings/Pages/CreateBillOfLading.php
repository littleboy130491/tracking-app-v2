<?php

/**
 * File: app/Filament/Resources/BillOfLadings/Pages/CreateBillOfLading.php
 * Responsibility: Creates a bill of lading.
 * What it does:
 * - Renders the document/event form and keeps the create action in the
 *   page header.
 * How to use: Reached from the Bill of lading resource "Create" button.
 * How to extend: Add post-create side effects in afterCreate().
 */

namespace App\Filament\Resources\BillOfLadings\Pages;

use App\Filament\Resources\BillOfLadings\BillOfLadingResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBillOfLading extends CreateRecord
{
    protected static string $resource = BillOfLadingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->getCreateFormAction(),
        ];
    }
}
