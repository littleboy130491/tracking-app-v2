<?php

/**
 * File: app/Filament/Resources/ImportShipments/Tables/ImportShipmentsTable.php
 * Responsibility: Admin list of import shipments.
 * What it does:
 * - Shows the identifying and status columns plus the customs billing
 *   response, with filters for status, response, company and created date,
 *   plus a soft-delete filter.
 * - Lists each container number as a badge linking to that container's edit page.
 * - Offers a CSV header action, restricted to admin/super_admin via
 *   User::canExportTables().
 * - Offers a "Prune old data" header action for the same roles, deleting
 *   import B/Ls older than the retention window.
 * How to use: Rendered by ListImportShipments.
 * How to extend: Add filters/columns as reporting needs grow.
 */

namespace App\Filament\Resources\ImportShipments\Tables;

use App\Enums\BillingResponse;
use App\Enums\ShipmentStatus;
use App\Filament\Concerns\DateFilters;
use App\Filament\Concerns\PrunableTableHeaderAction;
use App\Filament\Concerns\TableExportColumns;
use App\Filament\Resources\ImportContainers\ImportContainerResource;
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

class ImportShipmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with(['containers', 'hsCodes', 'company', 'creator', 'updater', 'documentReceivedBy']))
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
                    ->color(fn (BillingResponse $state): string => $state->color())
                    ->placeholder('—')
                    ->sortable(),
                TextColumn::make('containers.container_number')
                    ->label('Containers')
                    ->badge()
                    ->placeholder('—')
                    ->searchable()
                    ->url(function (mixed $state, ImportShipment $record): ?string {
                        if (! filled($state)) {
                            return null;
                        }

                        $container = $record->containers->firstWhere('container_number', $state);

                        return $container
                            ? ImportContainerResource::getUrl('edit', ['record' => $container])
                            : null;
                    }),
                TextColumn::make('port_of_loading')
                    ->label('Loading')
                    ->searchable()
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('port_of_discharge')
                    ->label('Discharge')
                    ->searchable()
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
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
                ...DateFilters::make(ImportShipment::class),
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->headerActions([
                ExportAction::make()
                    ->exports([
                        ExcelExport::make()->withColumns(TableExportColumns::for(TableExportColumns::IMPORT_SHIPMENTS)),
                    ])
                    ->visible(fn (): bool => (bool) auth()->user()?->canExportTables()),
                PrunableTableHeaderAction::make('import-shipments'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make()
                        ->visible(fn (): bool => (bool) auth()->user()?->can('ForceDeleteAny:ImportShipment')),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
