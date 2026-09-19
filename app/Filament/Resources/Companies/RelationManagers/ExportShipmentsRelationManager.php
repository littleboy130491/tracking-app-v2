<?php

/**
 * File: app/Filament/Resources/Companies/RelationManagers/ExportShipmentsRelationManager.php
 * Responsibility: Show one company's export shipments.
 * What it does:
 * - Lists the company's export shipments read-only; "Open" jumps to the full
 *   shipment edit page.
 * How to use: Rendered on the company edit page.
 * How to extend: Add columns/filters to mirror ExportShipmentsTable.
 */

namespace App\Filament\Resources\Companies\RelationManagers;

use App\Enums\ShipmentStatus;
use App\Filament\Resources\ExportShipments\ExportShipmentResource;
use App\Models\ExportShipment;
use Filament\Actions\Action;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class ExportShipmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'exportShipments';

    protected static ?string $title = 'Export Shipments';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('reference_number')
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
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (ShipmentStatus $state): string => $state->label())
                    ->color(fn (ShipmentStatus $state): string => $state->color())
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
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options(ShipmentStatus::options()),
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
