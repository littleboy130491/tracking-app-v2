<?php

/**
 * File: app/Filament/Resources/Containers/ContainerResource.php
 * Responsibility: Admin resource for containers.
 * What it does:
 * - Exposes CRUD for shipment containers, independent of the B/L screen.
 * How to use: Admin panel → Shipments → Containers.
 * How to extend: Add relation managers (attachments, location updates) here.
 */

namespace App\Filament\Resources\Containers;

use App\Filament\Resources\Containers\Pages\CreateContainer;
use App\Filament\Resources\Containers\Pages\EditContainer;
use App\Filament\Resources\Containers\Pages\ListContainers;
use App\Filament\Resources\Containers\Schemas\ContainerForm;
use App\Filament\Resources\Containers\Tables\ContainersTable;
use App\Models\Container;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class ContainerResource extends Resource
{
    protected static ?string $model = Container::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCube;

    protected static string|UnitEnum|null $navigationGroup = 'Shipments';

    protected static ?int $navigationSort = 20;

    protected static ?string $recordTitleAttribute = 'container_number';

    public static function form(Schema $schema): Schema
    {
        return ContainerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ContainersTable::configure($table);
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
            'index' => ListContainers::route('/'),
            'create' => CreateContainer::route('/create'),
            'edit' => EditContainer::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
