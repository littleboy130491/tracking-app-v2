<?php

/**
 * File: app/Filament/Resources/ImportContainers/Schemas/ImportContainerForm.php
 * Responsibility: Admin form for a standalone import container.
 * What it does:
 * - Edits the container fields (identity, size, gross weight, CBM, cargo,
 *   driver, photos, gate out, tracking, factory loading/return and status)
 *   per IMPORT.md.
 * - Milestone gating happens on the shipment form's repeater; the standalone
 *   form edits the same fields freely.
 * How to use: Rendered by the import container create/edit pages.
 * How to extend: add fields via ContainerFields so the shipment repeater stays
 *   in sync.
 */

namespace App\Filament\Resources\ImportContainers\Schemas;

use App\Filament\Concerns\ContainerFields;
use App\Models\ImportContainer;
use App\Models\ImportShipment;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class ImportContainerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Shipment')
                    ->columns(2)
                    ->schema([
                        Select::make('import_shipment_id')
                            ->label('Import shipment')
                            ->relationship('shipment', 'bl_number', modifyQueryUsing: fn (Builder $query): Builder => User::scopeToAssignedCompanies($query, 'company_id'))
                            ->getOptionLabelFromRecordUsing(fn (ImportShipment $record): string => $record->pickerLabel())
                            ->searchable()
                            ->preload()
                            ->required(),
                    ]),
                Section::make('Container')
                    ->columns(3)
                    ->schema([
                        ...ContainerFields::importIdentity(),
                        ...ContainerFields::importSize(),
                        ...ContainerFields::importWeights(),
                        ...ContainerFields::importCbm(),
                        ...ContainerFields::importCargo(),
                    ]),
                Section::make('Driver')
                    ->columns(3)
                    ->schema(ContainerFields::importDriver()),
                Section::make('Photos')
                    ->description('Named photo slots for this container.')
                    ->columns(2)
                    ->schema(ContainerFields::photos(ImportContainer::class)),
                Section::make('Gate out')
                    ->columns(3)
                    ->schema(ContainerFields::importGateOut()),
                Section::make('Tracking')
                    ->columns(3)
                    ->schema(ContainerFields::importTracking()),
                Section::make('Factory loading & return')
                    ->columns(3)
                    ->schema([
                        ...ContainerFields::importFactoryLoading(),
                        ...ContainerFields::importReturn(),
                    ]),
                Section::make('Status')
                    ->columns(3)
                    ->schema(ContainerFields::status()),
                ContainerFields::notesSection(),
            ]);
    }
}
