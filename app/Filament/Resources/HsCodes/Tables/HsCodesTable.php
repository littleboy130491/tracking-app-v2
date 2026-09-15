<?php

/**
 * File: app/Filament/Resources/HsCodes/Tables/HsCodesTable.php
 * Responsibility: Admin table for HS codes.
 * What it does:
 * - Lists each code, its description and how many shipments use it.
 * How to use: Rendered by the HS-code list page.
 * How to extend: Add filters or a link into the B/Ls that use a code.
 */

namespace App\Filament\Resources\HsCodes\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class HsCodesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('HS code')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('description')
                    ->limit(80)
                    ->searchable(),
                TextColumn::make('bill_of_ladings_count')
                    ->label('Shipments')
                    ->counts('billOfLadings')
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
