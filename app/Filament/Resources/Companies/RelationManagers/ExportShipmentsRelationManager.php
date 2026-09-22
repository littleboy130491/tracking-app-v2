<?php

/**
 * File: app/Filament/Resources/Companies/RelationManagers/ExportShipmentsRelationManager.php
 * Responsibility: Show one company's export shipments.
 * What it does:
 * - Lists the company's export shipments read-only; "Open" jumps to the full
 *   shipment edit page. Container numbers link to the container edit page.
 * - Filters by status and created date (year / month / range).
 * How to use: Rendered on the company edit page.
 * How to extend: Add columns/filters to mirror ExportShipmentsTable.
 */

namespace App\Filament\Resources\Companies\RelationManagers;

use App\Enums\ShipmentStatus;
use App\Filament\Concerns\DateFilters;
use App\Filament\Resources\ExportContainers\ExportContainerResource;
use App\Filament\Resources\ExportShipments\ExportShipmentResource;
use App\Models\ExportShipment;
use Filament\Actions\Action;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ExportShipmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'exportShipments';

    protected static ?string $title = 'Export Shipments';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('bl_number')
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with('containers'))
            ->columns([
                TextColumn::make('bl_number')
                    ->label('B/L number')
                    ->searchable()
                    ->placeholder('—')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (ShipmentStatus $state): string => $state->label())
                    ->color(fn (ShipmentStatus $state): string => $state->color())
                    ->sortable(),
                TextColumn::make('containers.container_number')
                    ->label('Containers')
                    ->badge()
                    ->placeholder('—')
                    ->url(function (mixed $state, ExportShipment $record): ?string {
                        if (! filled($state)) {
                            return null;
                        }

                        $container = $record->containers->firstWhere('container_number', $state);

                        return $container
                            ? ExportContainerResource::getUrl('edit', ['record' => $container])
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
                ...DateFilters::make(ExportShipment::class),
                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('open')
                    ->label('Open')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (ExportShipment $record): string => ExportShipmentResource::getUrl('edit', ['record' => $record]))
                    ->openUrlInNewTab(),
            ]);
    }
}
