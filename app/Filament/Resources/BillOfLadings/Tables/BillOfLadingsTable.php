<?php

/**
 * File: app/Filament/Resources/BillOfLadings/Tables/BillOfLadingsTable.php
 * Responsibility: Admin list of shipments.
 * What it does:
 * - Shows the identifying and status columns, with filters for shipment type,
 *   status, billing response and company, plus a soft-delete filter.
 * How to use: Rendered by ListBillOfLadings.
 * How to extend: Add filters/columns as reporting needs grow.
 */

namespace App\Filament\Resources\BillOfLadings\Tables;

use App\Enums\BillingResponse;
use App\Enums\BillOfLadingStatus;
use App\Enums\ShipmentType;
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

class BillOfLadingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reference_number')
                    ->label('Reference')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('bl_number')
                    ->label('B/L number')
                    ->searchable()
                    ->placeholder('—')
                    ->sortable(),
                TextColumn::make('shipment_type')
                    ->badge()
                    ->formatStateUsing(fn (ShipmentType $state): string => $state->label())
                    ->sortable(),
                TextColumn::make('company.name')
                    ->label('Company')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (BillOfLadingStatus $state): string => $state->label())
                    ->color(fn (BillOfLadingStatus $state): string => $state->color())
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
                TextColumn::make('departure_date')
                    ->label('Departure')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('shipment_type')
                    ->options(ShipmentType::options()),
                SelectFilter::make('status')
                    ->options(BillOfLadingStatus::options()),
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
