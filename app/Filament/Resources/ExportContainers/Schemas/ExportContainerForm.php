<?php

/**
 * File: app/Filament/Resources/ExportContainers/Schemas/ExportContainerForm.php
 * Responsibility: Admin form for a standalone export container.
 * What it does:
 * - Selects the parent export shipment, then edits the export container
 *   fields (identity, transport/tracking, photos, stuffing, gate in, VGM,
 *   final check and status).
 * - Milestone gating happens on the shipment form's repeater; the standalone
 *   form edits the same fields freely.
 * How to use: Rendered by the export container create/edit pages.
 * How to extend: add fields via ContainerFields so the shipment repeater stays
 *   in sync.
 */

namespace App\Filament\Resources\ExportContainers\Schemas;

use App\Filament\Concerns\ContainerFields;
use App\Models\ExportContainer;
use App\Models\ExportShipment;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class ExportContainerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Shipment')
                    ->columns(2)
                    ->schema([
                        Select::make('export_shipment_id')
                            ->label('Export shipment')
                            ->relationship('shipment', 'bl_number', modifyQueryUsing: fn (Builder $query): Builder => User::scopeToAssignedCompanies($query, 'company_id'))
                            ->searchable()
                            ->preload()
                            ->required(),
                    ]),
                Section::make('Container')
                    ->columns(3)
                    ->schema(ContainerFields::identity()),
                Section::make('Transport')
                    ->columns(3)
                    ->schema([
                        ...ContainerFields::driver(),
                        Grid::make(2)
                            ->columnSpanFull()
                            ->schema(ContainerFields::exportTracking()),
                    ]),
                Section::make('Photos')
                    ->description('Named photo slots for this container.')
                    ->columns(2)
                    ->schema(ContainerFields::photos(ExportContainer::class)),
                Section::make('Stuffing')
                    ->description('Process 2/3: stuff the container at the factory.')
                    ->columns(3)
                    ->schema(ContainerFields::exportStuffing()),
                Section::make('Gate in & VGM')
                    ->columns(3)
                    ->schema([
                        // EXPORT.md: the gate-in port starts as the shipment's port of loading.
                        ...ContainerFields::exportGateIn(fn (Get $get): ?string => ExportShipment::query()->find($get('export_shipment_id'))?->port_of_loading),
                        ...ContainerFields::exportVgm(),
                    ]),
                Section::make('Final check')
                    ->columns(3)
                    ->schema(ContainerFields::exportFinalCheck()),
                ContainerFields::notesSection(),
            ]);
    }
}
