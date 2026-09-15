<?php

/**
 * File: app/Filament/Resources/HsCodes/Pages/CreateHsCode.php
 * Responsibility: Creates an HS code.
 * What it does:
 * - Renders the code/description form and keeps the create action in the
 *   page header.
 * How to use: Reached from the HS-code resource "Create" button.
 */

namespace App\Filament\Resources\HsCodes\Pages;

use App\Filament\Resources\HsCodes\HsCodeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateHsCode extends CreateRecord
{
    protected static string $resource = HsCodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->getCreateFormAction(),
        ];
    }
}
