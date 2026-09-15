<?php

/**
 * File: app/Filament/Resources/ActivityLogs/Pages/ListActivityLogs.php
 * Responsibility: Index page for the read-only activity log.
 * What it does:
 * - Lists audit entries; no create action is offered (logs are system-written).
 * How to use: Reached from the Activity Logs navigation item.
 * How to extend: Add exports or date-range widgets here.
 */

namespace App\Filament\Resources\ActivityLogs\Pages;

use App\Filament\Resources\ActivityLogs\ActivityLogResource;
use Filament\Resources\Pages\ListRecords;

class ListActivityLogs extends ListRecords
{
    protected static string $resource = ActivityLogResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
