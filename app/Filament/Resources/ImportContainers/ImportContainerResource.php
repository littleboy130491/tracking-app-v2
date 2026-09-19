<?php

/**
 * File: app/Filament/Resources/ImportContainers/ImportContainerResource.php
 * Responsibility: Admin resource for import containers.
 * What it does:
 * - Exposes CRUD for import containers, independent of the shipment screen.
 * How to use: Admin panel → Containers → Import.
 * How to extend: Add relation managers here as container attachments grow.
 */

namespace App\Filament\Resources\ImportContainers;

use App\Filament\Resources\ImportContainers\Pages\CreateImportContainer;
use App\Filament\Resources\ImportContainers\Pages\EditImportContainer;
use App\Filament\Resources\ImportContainers\Pages\ListImportContainers;
use App\Filament\Resources\ImportContainers\Schemas\ImportContainerForm;
use App\Filament\Resources\ImportContainers\Tables\ImportContainersTable;
use App\Models\ImportContainer;
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

class ImportContainerResource extends Resource
{
    protected static ?string $model = ImportContainer::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCube;

    protected static string|UnitEnum|null $navigationGroup = 'Containers';

    protected static ?int $navigationSort = 20;

    protected static ?string $navigationLabel = 'Import';

    protected static ?string $recordTitleAttribute = 'container_number';

    public static function form(Schema $schema): Schema
    {
        return ImportContainerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ImportContainersTable::configure($table);
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
            'index' => ListImportContainers::route('/'),
            'create' => CreateImportContainer::route('/create'),
            'edit' => EditImportContainer::route('/{record}/edit'),
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
