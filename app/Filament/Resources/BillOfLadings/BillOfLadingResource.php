<?php

/**
 * File: app/Filament/Resources/BillOfLadings/BillOfLadingResource.php
 * Responsibility: Admin resource for bills of lading.
 * What it does:
 * - Exposes CRUD for shipments; containers and HS codes are edited inline
 *   through repeater fields on the form instead of relation managers.
 * How to use: Admin panel → Shipments → Bills of lading.
 * How to extend: Register relation managers in getRelations() or add form
 *   sections in BillOfLadingForm.
 */

namespace App\Filament\Resources\BillOfLadings;

use App\Filament\Resources\BillOfLadings\Pages\CreateBillOfLading;
use App\Filament\Resources\BillOfLadings\Pages\EditBillOfLading;
use App\Filament\Resources\BillOfLadings\Pages\ListBillOfLadings;
use App\Filament\Resources\BillOfLadings\Schemas\BillOfLadingForm;
use App\Filament\Resources\BillOfLadings\Tables\BillOfLadingsTable;
use App\Models\BillOfLading;
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

class BillOfLadingResource extends Resource
{
    protected static ?string $model = BillOfLading::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static string|UnitEnum|null $navigationGroup = 'Shipments';

    protected static ?int $navigationSort = 10;

    protected static ?string $recordTitleAttribute = 'reference_number';

    public static function form(Schema $schema): Schema
    {
        return BillOfLadingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BillOfLadingsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBillOfLadings::route('/'),
            'create' => CreateBillOfLading::route('/create'),
            'edit' => EditBillOfLading::route('/{record}/edit'),
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
