<?php

/**
 * File: app/Filament/Resources/ActivityLogs/Pages/ViewActivityLog.php
 * Responsibility: Read-only detail page for one audit entry.
 * What it does:
 * - Shows the recorded event, actor, entity and before/after values.
 * How to use: Reached by clicking an entry in the activity log list.
 * How to extend: Add related-record links for easier navigation.
 */

namespace App\Filament\Resources\ActivityLogs\Pages;

use App\Filament\Resources\ActivityLogs\ActivityLogResource;
use Filament\Resources\Pages\ViewRecord;

class ViewActivityLog extends ViewRecord
{
    protected static string $resource = ActivityLogResource::class;
}
