<?php

/**
 * File: app/Filament/Resources/HsCodes/Tables/HsCodesTable.php
 * Responsibility: Admin table for HS codes.
 * What it does:
 * - Lists each code, its description and how many shipments use it.
 * - Offers a CSV header action, restricted to admin/super_admin via
 *   User::canExportTables().
 * - Offers a "Prune old data" header action, restricted to super_admin,
 *   deleting HS codes older than the retention window.
 * - Soft delete: admin/super_admin may trash and restore; the force-delete
 *   action is hidden unless the user may permanently delete (super_admin).
 * How to use: Rendered by the HS-code list page.
 * How to extend: Add filters or a link into the B/Ls that use a code.
 */

namespace App\Filament\Resources\HsCodes\Tables;

use App\Filament\Concerns\PrunableTableHeaderAction;
use App\Filament\Concerns\TableExportColumns;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use pxlrbt\FilamentExcel\Actions\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

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
                TextColumn::make('export_shipments_count')
                    ->label('Export shipments')
                    ->counts('exportShipments')
                    ->sortable(),
                TextColumn::make('import_shipments_count')
                    ->label('Import shipments')
                    ->counts('importShipments')
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->headerActions([
                ExportAction::make()
                    ->exports([
                        ExcelExport::make()->withColumns(TableExportColumns::for(TableExportColumns::HS_CODES)),
                    ])
                    ->visible(fn (): bool => (bool) auth()->user()?->canExportTables()),
                PrunableTableHeaderAction::make('hs-codes'),
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
