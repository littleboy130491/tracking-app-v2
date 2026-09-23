<?php

/**
 * File: app/Filament/Resources/ActivityLogs/Tables/ActivityLogsTable.php
 * Responsibility: Read-only list of audit entries.
 * What it does:
 * - Shows when/what/who changed, with filters by event and actor.
 * - Offers only a view action; logs are never edited or deleted.
 * How to use: Rendered by ListActivityLogs.
 * How to extend: Add columns for new audit fields.
 */

namespace App\Filament\Resources\ActivityLogs\Tables;

use App\Models\ActivityLog;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ActivityLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('occurred_at')
                    ->label('When')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('event')
                    ->badge()
                    ->searchable(),
                TextColumn::make('shipment_reference')
                    ->label('Shipment')
                    ->state(fn (ActivityLog $record): ?string => $record->linkedShipment()?->bl_number)
                    ->placeholder('—'),
                TextColumn::make('container_number')
                    ->label('Container')
                    ->state(fn (ActivityLog $record): ?string => $record->linkedContainer()?->container_number)
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('actor.name')
                    ->label('Actor')
                    ->placeholder('system')
                    ->searchable(),
                TextColumn::make('entity_type')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('customer_summary')
                    ->label('Customer summary')
                    ->limit(60)
                    ->toggleable(),
            ])
            ->defaultSort('occurred_at', 'desc')
            ->filters([
                SelectFilter::make('event')
                    ->options(fn (): array => ActivityLog::query()
                        ->distinct()
                        ->orderBy('event')
                        ->pluck('event', 'event')
                        ->all())
                    ->multiple(),
                SelectFilter::make('actor_id')
                    ->label('Actor')
                    ->relationship('actor', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
