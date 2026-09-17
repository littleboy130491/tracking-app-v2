<?php

/**
 * File: app/Filament/Resources/Containers/Tables/ContainersTable.php
 * Responsibility: Admin list of containers.
 * What it does:
 * - Shows the container identity, its B/L and the operational statuses, with
 *   filters for status, stuffing and inspection plus a soft-delete filter.
 * How to use: Rendered by ListContainers.
 * How to extend: Add columns as container tracking grows.
 */

namespace App\Filament\Resources\Containers\Tables;

use App\Enums\ContainerStatus;
use App\Enums\InspectionStatus;
use App\Enums\StuffingStatus;
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

class ContainersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('container_number')
                    ->label('Container')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('billOfLading.reference_number')
                    ->label('B/L reference')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('billOfLading.company.name')
                    ->label('Company')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('size')
                    ->placeholder('—')
                    ->badge(),
                TextColumn::make('type')
                    ->placeholder('—')
                    ->badge(),
                TextColumn::make('seal_number')
                    ->label('Seal')
                    ->placeholder('—')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (ContainerStatus $state): string => $state->label())
                    ->color(fn (ContainerStatus $state): string => $state->color())
                    ->sortable(),
                TextColumn::make('stuffing_status')
                    ->label('Stuffing')
                    ->badge()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('inspection_status')
                    ->label('Inspection')
                    ->badge()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('empty_returned_at')
                    ->label('Empty returned')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('container_number')
            ->filters([
                SelectFilter::make('status')->options(ContainerStatus::options()),
                SelectFilter::make('stuffing_status')->options(StuffingStatus::options()),
                SelectFilter::make('inspection_status')->options(InspectionStatus::options()),
                SelectFilter::make('bill_of_lading_id')
                    ->label('Bill of lading')
                    ->relationship('billOfLading', 'reference_number', modifyQueryUsing: fn (Builder $query): Builder => User::scopeToAssignedCompanies($query, 'company_id'))
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
