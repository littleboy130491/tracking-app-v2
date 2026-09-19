<?php

/**
 * File: app/Filament/Resources/ExportContainers/Tables/ExportContainersTable.php
 * Responsibility: Admin list of export containers.
 * What it does:
 * - Shows the container identity, its shipment and the export statuses, with
 *   filters for status, stuffing and shipment plus a soft-delete filter.
 * How to use: Rendered by ListExportContainers.
 * How to extend: Add columns as container tracking grows.
 */

namespace App\Filament\Resources\ExportContainers\Tables;

use App\Enums\ContainerStatus;
use App\Enums\StuffingStatus;
use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ExportContainersTable
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
                TextColumn::make('stuffing_status')
                    ->label('Stuffing')
                    ->badge()
                    ->sortable()
                    ->toggleable(),
                IconColumn::make('final_checked')
                    ->label('Final checked')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('container_number')
            ->filters([
                SelectFilter::make('status')->options(ContainerStatus::options()),
                SelectFilter::make('stuffing_status')->options(StuffingStatus::options()),
                SelectFilter::make('export_shipment_id')
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
