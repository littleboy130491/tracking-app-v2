<?php

/**
 * File: app/Filament/Resources/ActivityLogs/ActivityLogResource.php
 * Responsibility: Read-only audit log viewer (spec.md: "view only logs").
 * What it does:
 * - Exposes the append-only activity_logs table for staff inspection only;
 *   create, edit and delete are intentionally not registered.
 * How to use: Navigate to the Activity Logs resource in the admin panel.
 * How to extend: Add infolist entries/columns, never write actions.
 */

namespace App\Filament\Resources\ActivityLogs;

use App\Filament\Resources\ActivityLogs\Pages\ListActivityLogs;
use App\Filament\Resources\ActivityLogs\Pages\ViewActivityLog;
use App\Filament\Resources\ActivityLogs\Schemas\ActivityLogForm;
use App\Filament\Resources\ActivityLogs\Tables\ActivityLogsTable;
use App\Models\ActivityLog;
use App\Models\Role;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class ActivityLogResource extends Resource
{
    protected static ?string $model = ActivityLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static string|UnitEnum|null $navigationGroup = 'Monitoring';

    protected static ?int $navigationSort = 90;

    public static function form(Schema $schema): Schema
    {
        return ActivityLogForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ActivityLogsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListActivityLogs::route('/'),
            'view' => ViewActivityLog::route('/{record}'),
        ];
    }

    /**
     * Row-level scope: privileged staff see every entry; operators only
     * entries about a visible shipment (container entries carry the shipment
     * link too). Entries with no shipment link (global logs) stay hidden.
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->with([
                'exportShipment:id,reference_number',
                'importShipment:id,reference_number',
                'exportContainer:id,container_number',
                'importContainer:id,container_number',
            ]);
        $user = auth()->user();

        if (! $user instanceof User || $user->hasAnyRole(Role::PRIVILEGED)) {
            return $query;
        }

        $companyIds = $user->companyIds();

        return $query->where(function (Builder $query) use ($companyIds): void {
            $query->whereHas('exportShipment', fn (Builder $query) => $query->whereIn('company_id', $companyIds))
                ->orWhereHas('importShipment', fn (Builder $query) => $query->whereIn('company_id', $companyIds));
        });
    }
}
