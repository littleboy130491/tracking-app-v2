<?php

/**
 * File: app/Filament/Resources/ImportContainers/Tables/ImportContainersTable.php
 * Responsibility: Admin list of import containers.
 * What it does:
 * - Shows the container identity, its shipment and the import statuses, with
 *   filters for status, inspection and shipment plus a soft-delete filter.
 * How to use: Rendered by ListImportContainers.
 * How to extend: Add columns as container tracking grows.
 */

namespace App\Filament\Resources\ImportContainers\Tables;

use App\Enums\ContainerStatus;
use App\Enums\InspectionStatus;
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

class ImportContainersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('container_number')
                    ->label('Container')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('shipment.reference_number')
                    ->label('Shipment')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('shipment.company.name')
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
                SelectFilter::make('inspection_status')->options(InspectionStatus::options()),
                SelectFilter::make('import_shipment_id')
                    ->label('Shipment')
                    ->relationship('shipment', 'reference_number', modifyQueryUsing: fn (Builder $query): Builder => User::scopeToAssignedCompanies($query, 'company_id'))
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
