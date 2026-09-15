<?php

/**
 * File: app/Filament/Resources/HsCodes/Pages/EditHsCode.php
 * Responsibility: Edits an HS code.
 * What it does:
 * - Renders the code/description form; the save action sits in the page
 *   header.
 * How to use: Reached by editing an HS code.
 */

namespace App\Filament\Resources\HsCodes\Pages;

use App\Filament\Resources\HsCodes\HsCodeResource;
use Filament\Resources\Pages\EditRecord;

class EditHsCode extends EditRecord
{
    protected static string $resource = HsCodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->getSaveFormAction(),
        ];
    }
}
