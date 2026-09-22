<?php

/**
 * File: app/Filament/Resources/ExportContainers/Tables/ExportContainersTable.php
 * Responsibility: Admin list of export containers.
 * What it does:
 * - Shows the container identity, its shipment and the export statuses
 *   (company links to the company edit page), with filters for status,
 *   stuffing, company, shipment and created date plus a soft-delete filter.
 * - Offers a CSV header action, restricted to admin/super_admin via
 *   User::canExportTables().
 * - Offers a "Prune old data" header action for the same roles, deleting
 *   export containers older than the retention window.
 * How to use: Rendered by ListExportContainers.
 * How to extend: Add columns as container tracking grows.
 */

namespace App\Filament\Resources\ExportContainers\Tables;

use App\Enums\ContainerStatus;
use App\Enums\StuffingStatus;
use App\Filament\Concerns\DateFilters;
use App\Filament\Concerns\PrunableTableHeaderAction;
use App\Filament\Concerns\TableExportColumns;
use App\Filament\Resources\Companies\CompanyResource;
use App\Models\Company;
use App\Models\ExportContainer;
use App\Models\ExportShipment;
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
use pxlrbt\FilamentExcel\Actions\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class ExportContainersTable
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
                    ->sortable()
                    ->url(fn (ExportContainer $record): ?string => $record->shipment?->company === null
                        ? null
                        : CompanyResource::getUrl('edit', ['record' => $record->shipment->company]))
                    ->toggleable(),
                TextColumn::make('size')
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
                SelectFilter::make('company')
                    ->label('Company')
                    ->options(fn (): array => Company::query()
                        ->whereIn('id', User::scopeToAssignedCompanies(Company::query())->pluck('id'))
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all())
                    ->query(fn (Builder $query, array $data): Builder => blank($data['value'])
                        ? $query
                        : $query->whereHas('shipment', fn (Builder $shipment): Builder => $shipment->where('company_id', $data['value'])))
                    ->searchable(),
                SelectFilter::make('stuffing_status')->options(StuffingStatus::options()),
                SelectFilter::make('export_shipment_id')
                    ->label('Shipment')
                    ->relationship('shipment', 'bl_number', modifyQueryUsing: fn (Builder $query): Builder => User::scopeToAssignedCompanies($query, 'company_id'))
                    ->getOptionLabelFromRecordUsing(fn (ExportShipment $record): string => $record->pickerLabel())
                    ->searchable()
                    ->preload(),
                ...DateFilters::make(ExportContainer::class),
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->headerActions([
                ExportAction::make()
                    ->exports([
                        ExcelExport::make()->withColumns(TableExportColumns::for(TableExportColumns::EXPORT_CONTAINERS)),
                    ])
                    ->visible(fn (): bool => (bool) auth()->user()?->canExportTables()),
                PrunableTableHeaderAction::make('export-containers'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make()
                        ->visible(fn (): bool => (bool) auth()->user()?->can('ForceDeleteAny:ExportContainer')),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
