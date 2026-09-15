<?php

/**
 * File: app/Filament/Resources/HsCodes/Pages/ListHsCodes.php
 * Responsibility: Lists HS codes with a create action in the header.
 * How to use: The resource's index route.
 */

namespace App\Filament\Resources\HsCodes\Pages;

use App\Filament\Resources\HsCodes\HsCodeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListHsCodes extends ListRecords
{
    protected static string $resource = HsCodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
