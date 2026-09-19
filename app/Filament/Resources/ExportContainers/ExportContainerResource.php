<?php

/**
 * File: app/Filament/Resources/ExportContainers/ExportContainerResource.php
 * Responsibility: Admin resource for export containers.
 * What it does:
 * - Exposes CRUD for export containers, independent of the shipment screen.
 * How to use: Admin panel → Containers → Export.
 * How to extend: Add relation managers here as container attachments grow.
 */

namespace App\Filament\Resources\ExportContainers;

use App\Filament\Resources\ExportContainers\Pages\CreateExportContainer;
use App\Filament\Resources\ExportContainers\Pages\EditExportContainer;
use App\Filament\Resources\ExportContainers\Pages\ListExportContainers;
use App\Filament\Resources\ExportContainers\Schemas\ExportContainerForm;
use App\Filament\Resources\ExportContainers\Tables\ExportContainersTable;
use App\Models\ExportContainer;
use App\Models\Role;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class ExportContainerResource extends Resource
{
    protected static ?string $model = ExportContainer::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCube;

    protected static string|UnitEnum|null $navigationGroup = 'Containers';

    protected static ?int $navigationSort = 10;

    protected static ?string $navigationLabel = 'Export';

    protected static ?string $recordTitleAttribute = 'container_number';

    public static function form(Schema $schema): Schema
    {
        return ExportContainerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ExportContainersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListExportContainers::route('/'),
            'create' => CreateExportContainer::route('/create'),
            'edit' => EditExportContainer::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    /**
     * Row-level scope: privileged staff see every container; operators only
     * containers whose shipment belongs to an assigned company.
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if (! $user instanceof User || $user->hasAnyRole(Role::PRIVILEGED)) {
            return $query;
        }

        return $query->whereHas('shipment', fn (Builder $query) => $query->whereIn('company_id', $user->companyIds()));
    }
}
