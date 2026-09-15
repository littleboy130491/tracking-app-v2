<?php

/**
 * File: app/Filament/Resources/HsCodes/HsCodeResource.php
 * Responsibility: Admin resource for HS codes (shared master data).
 * What it does:
 * - Exposes CRUD for HS codes; bills of lading attach them through the
 *   multi-select on the B/L form, which can also create codes inline.
 * How to use: Admin panel → Shipments → HS codes.
 * How to extend: Add a relation manager listing the B/Ls using a code.
 */

namespace App\Filament\Resources\HsCodes;

use App\Filament\Resources\HsCodes\Pages\CreateHsCode;
use App\Filament\Resources\HsCodes\Pages\EditHsCode;
use App\Filament\Resources\HsCodes\Pages\ListHsCodes;
use App\Filament\Resources\HsCodes\Schemas\HsCodeForm;
use App\Filament\Resources\HsCodes\Tables\HsCodesTable;
use App\Models\HsCode;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class HsCodeResource extends Resource
{
    protected static ?string $model = HsCode::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static string|UnitEnum|null $navigationGroup = 'Shipments';

    protected static ?int $navigationSort = 30;

    protected static ?string $recordTitleAttribute = 'code';

    public static function form(Schema $schema): Schema
    {
        return HsCodeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HsCodesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListHsCodes::route('/'),
            'create' => CreateHsCode::route('/create'),
            'edit' => EditHsCode::route('/{record}/edit'),
        ];
    }
}
