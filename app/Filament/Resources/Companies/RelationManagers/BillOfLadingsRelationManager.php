<?php

/**
 * File: app/Filament/Resources/Companies/RelationManagers/BillOfLadingsRelationManager.php
 * Responsibility: Show one company's bill of ladings.
 * What it does:
 * - Lists the company's shipments read-only; "Open" jumps to the full B/L
 *   edit page.
 * How to use: Rendered on the company edit page.
 * How to extend: Add columns/filters to mirror BillOfLadingsTable.
 */

namespace App\Filament\Resources\Companies\RelationManagers;

use App\Enums\BillingResponse;
use App\Enums\BillOfLadingStatus;
use App\Enums\ShipmentType;
use App\Filament\Resources\BillOfLadings\BillOfLadingResource;
use App\Models\BillOfLading;
use Filament\Actions\Action;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class BillOfLadingsRelationManager extends RelationManager
{
    protected static string $relationship = 'billOfLadings';

    protected static ?string $title = 'Bill of Ladings';

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
                TextColumn::make('shipment_type')
                    ->badge()
                    ->formatStateUsing(fn (ShipmentType $state): string => $state->label())
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (BillOfLadingStatus $state): string => $state->label())
                    ->color(fn (BillOfLadingStatus $state): string => $state->color())
                    ->sortable(),
                TextColumn::make('billing_response')
                    ->label('Response')
                    ->badge()
                    ->placeholder('—')
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
                SelectFilter::make('shipment_type')
                    ->options(ShipmentType::options()),
                SelectFilter::make('status')
                    ->options(BillOfLadingStatus::options()),
                SelectFilter::make('billing_response')
                    ->options(BillingResponse::options()),
                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('open')
                    ->label('Open')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (BillOfLading $record): string => BillOfLadingResource::getUrl('edit', ['record' => $record]))
                    ->openUrlInNewTab(),
            ]);
    }
}
