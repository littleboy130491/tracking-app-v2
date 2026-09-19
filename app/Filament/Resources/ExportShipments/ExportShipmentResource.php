<?php

/**
 * File: app/Filament/Resources/ExportShipments/ExportShipmentResource.php
 * Responsibility: Admin resource for export shipments.
 * What it does:
 * - Exposes CRUD for export shipments; containers and HS codes are edited
 *   inline through repeater fields on the form instead of relation managers.
 * How to use: Admin panel → Bill of Ladings → Export.
 * How to extend: Register relation managers in getRelations() or add form
 *   sections in ExportShipmentForm.
 */

namespace App\Filament\Resources\ExportShipments;

use App\Filament\Resources\ExportShipments\Pages\CreateExportShipment;
use App\Filament\Resources\ExportShipments\Pages\EditExportShipment;
use App\Filament\Resources\ExportShipments\Pages\ListExportShipments;
use App\Filament\Resources\ExportShipments\Schemas\ExportShipmentForm;
use App\Filament\Resources\ExportShipments\Tables\ExportShipmentsTable;
use App\Models\ExportShipment;
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

class ExportShipmentResource extends Resource
{
    protected static ?string $model = ExportShipment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowUpTray;

    protected static string|UnitEnum|null $navigationGroup = 'Bill of Ladings';

    protected static ?int $navigationSort = 10;

    protected static ?string $navigationLabel = 'Export';

    protected static ?string $recordTitleAttribute = 'reference_number';

    public static function form(Schema $schema): Schema
    {
        return ExportShipmentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ExportShipmentsTable::configure($table);
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
            'index' => ListExportShipments::route('/'),
            'create' => CreateExportShipment::route('/create'),
            'edit' => EditExportShipment::route('/{record}/edit'),
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
     * Row-level scope: privileged staff see every shipment; everyone else
     * (operators) only the companies they are assigned to — the same rule
     * the customer portal applies through the company_user pivot.
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if (! $user instanceof User || $user->hasAnyRole(Role::PRIVILEGED)) {
            return $query;
        }

        return $query->whereIn('company_id', $user->companyIds());
    }
}
