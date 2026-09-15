<?php

/**
 * File: app/Filament/Resources/ActivityLogs/Schemas/ActivityLogForm.php
 * Responsibility: Read-only schema describing an audit entry.
 * What it does:
 * - Renders the event metadata and before/after values as JSON on the view page.
 * How to use: Used by ActivityLogResource::form() for the view screen.
 * How to extend: Add fields when new audit columns are introduced.
 */

namespace App\Filament\Resources\ActivityLogs\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ActivityLogForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Event')
                    ->columns(2)
                    ->schema([
                        TextInput::make('event')
                            ->disabled(),
                        TextInput::make('actor.name')
                            ->label('Actor')
                            ->disabled(),
                        TextInput::make('entity_type')
                            ->disabled(),
                        TextInput::make('entity_id')
                            ->disabled(),
                        DateTimePicker::make('occurred_at')
                            ->disabled(),
                        Toggle::make('is_customer_visible')
                            ->disabled(),
                    ]),
                Section::make('Context')
                    ->columns(2)
                    ->schema([
                        TextInput::make('billOfLading.reference_number')
                            ->label('Bill of lading')
                            ->disabled(),
                        TextInput::make('container.container_number')
                            ->label('Container')
                            ->disabled(),
                        Textarea::make('customer_summary')
                            ->disabled()
                            ->columnSpanFull(),
                    ]),
                Section::make('Changes')
                    ->columns(2)
                    ->schema([
                        Textarea::make('old_values')
                            ->formatStateUsing(fn ($state): string => json_encode($state, JSON_PRETTY_PRINT) ?: '')
                            ->disabled()
                            ->rows(8),
                        Textarea::make('new_values')
                            ->formatStateUsing(fn ($state): string => json_encode($state, JSON_PRETTY_PRINT) ?: '')
                            ->disabled()
                            ->rows(8),
                    ]),
            ]);
    }
}
