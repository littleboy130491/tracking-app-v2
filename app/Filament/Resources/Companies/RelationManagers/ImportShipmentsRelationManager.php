<?php

/**
 * File: app/Filament/Resources/Companies/RelationManagers/ImportShipmentsRelationManager.php
 * Responsibility: Show one company's import shipments.
 * What it does:
 * - Lists the company's import shipments read-only; "Open" jumps to the full
 *   shipment edit page. Container numbers link to the container edit page.
 * How to use: Rendered on the company edit page.
 * How to extend: Add columns/filters to mirror ImportShipmentsTable.
 */

namespace App\Filament\Resources\Companies\RelationManagers;

use App\Enums\BillingResponse;
use App\Enums\ShipmentStatus;
use App\Filament\Resources\ImportContainers\ImportContainerResource;
use App\Filament\Resources\ImportShipments\ImportShipmentResource;
use App\Models\ImportShipment;
use Filament\Actions\Action;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ImportShipmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'importShipments';

    protected static ?string $title = 'Import Shipments';

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
                TextColumn::make('billing_response')
                    ->label('Response')
                    ->badge()
                    ->placeholder('—')
                    ->sortable(),
                TextColumn::make('containers.container_number')
                    ->label('Containers')
                    ->badge()
                    ->placeholder('—')
                    ->url(function (mixed $state, ImportShipment $record): ?string {
                        if (! filled($state)) {
                            return null;
                        }

                        $container = $record->containers->firstWhere('container_number', $state);

                        return $container
                            ? ImportContainerResource::getUrl('edit', ['record' => $container])
                            : null;
                    }),
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
                SelectFilter::make('billing_response')
                    ->options(BillingResponse::options()),
                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('open')
                    ->label('Open')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (ImportShipment $record): string => ImportShipmentResource::getUrl('edit', ['record' => $record]))
                    ->openUrlInNewTab(),
            ]);
    }
}
