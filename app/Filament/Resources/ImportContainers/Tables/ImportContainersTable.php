<?php

/**
 * File: app/Filament/Resources/ImportContainers/Tables/ImportContainersTable.php
 * Responsibility: Admin list of import containers.
 * What it does:
 * - Shows the container identity, its shipment and status, with filters for
 *   status and shipment plus a soft-delete filter.
 * - Offers a CSV header action, restricted to admin/super_admin via
 *   User::canExportTables().
 * - Offers a "Prune old data" header action for the same roles, deleting
 *   import containers older than the retention window.
 * How to use: Rendered by ListImportContainers.
 * How to extend: Add columns as container tracking grows.
 */

namespace App\Filament\Resources\ImportContainers\Tables;

use App\Enums\ContainerStatus;
use App\Filament\Concerns\PrunableTableHeaderAction;
use App\Filament\Concerns\TableExportColumns;
use App\Models\ImportShipment;
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
use pxlrbt\FilamentExcel\Actions\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class ImportContainersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with('shipment.company'))
            ->columns([
                TextColumn::make('container_number')
                    ->label('Container')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('shipment.bl_number')
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
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (ContainerStatus $state): string => $state->label())
                    ->color(fn (ContainerStatus $state): string => $state->color())
                    ->sortable(),
                TextColumn::make('empty_returned_at')
                    ->label('Empty returned')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('container_number')
            ->filters([
                SelectFilter::make('status')->options(ContainerStatus::options()),
                SelectFilter::make('import_shipment_id')
                    ->label('Shipment')
                    ->relationship('shipment', 'bl_number', modifyQueryUsing: fn (Builder $query): Builder => User::scopeToAssignedCompanies($query, 'company_id'))
                    ->getOptionLabelFromRecordUsing(fn (ImportShipment $record): string => $record->pickerLabel())
                    ->searchable()
                    ->preload(),
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->headerActions([
                ExportAction::make()
                    ->exports([
                        ExcelExport::make()->withColumns(TableExportColumns::for(TableExportColumns::IMPORT_CONTAINERS)),
                    ])
                    ->visible(fn (): bool => (bool) auth()->user()?->canExportTables()),
                PrunableTableHeaderAction::make('import-containers'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make()
                        ->visible(fn (): bool => (bool) auth()->user()?->can('ForceDeleteAny:ImportContainer')),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
