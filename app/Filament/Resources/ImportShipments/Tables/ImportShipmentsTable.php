<?php

/**
 * File: app/Filament/Resources/ImportShipments/Tables/ImportShipmentsTable.php
 * Responsibility: Admin list of import shipments.
 * What it does:
 * - Shows the identifying and status columns plus the customs billing
 *   response, with filters for status, response and company, and a
 *   soft-delete filter.
 * How to use: Rendered by ListImportShipments.
 * How to extend: Add filters/columns as reporting needs grow.
 */

namespace App\Filament\Resources\ImportShipments\Tables;

use App\Enums\BillingResponse;
use App\Enums\ShipmentStatus;
use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ImportShipmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('bl_number')
                    ->label('B/L number')
                    ->searchable()
                    ->placeholder('—')
                    ->sortable(),
                TextColumn::make('company.name')
                    ->label('Company')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (ShipmentStatus $state): string => $state->label())
                    ->color(fn (ShipmentStatus $state): string => $state->color())
                    ->sortable(),
                TextColumn::make('billing_response')
                    ->label('Response')
                    ->badge()
                    ->placeholder('—')
                    ->sortable(),
                TextColumn::make('containers_count')
                    ->label('Containers')
                    ->counts('containers')
                    ->badge(),
                TextColumn::make('eta_at')
                    ->label('ETA')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options(ShipmentStatus::options()),
                SelectFilter::make('billing_response')
                    ->options(BillingResponse::options()),
                SelectFilter::make('company')
                    ->relationship('company', 'name', modifyQueryUsing: fn (Builder $query): Builder => User::scopeToAssignedCompanies($query))
                    ->searchable()
                    ->preload(),
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
