<?php

/**
 * File: app/Filament/Resources/ImportContainers/Schemas/ImportContainerForm.php
 * Responsibility: Admin form for a standalone import container.
 * What it does:
 * - Selects the parent import shipment, then edits the import container
 *   fields (identity, driver, photos, gate out/weights, inspection, factory
 *   loading/return and status).
 * - Milestone gating happens on the shipment form's repeater; the standalone
 *   form edits the same fields freely.
 * How to use: Rendered by the import container create/edit pages.
 * How to extend: add fields via ContainerFields so the shipment repeater stays
 *   in sync.
 */

namespace App\Filament\Resources\ImportContainers\Schemas;

use App\Filament\Concerns\ContainerFields;
use App\Models\ImportContainer;
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
                            ->relationship('shipment', 'reference_number', modifyQueryUsing: fn (Builder $query): Builder => User::scopeToAssignedCompanies($query, 'company_id'))
                            ->searchable()
                            ->preload()
                            ->required(),
                    ]),
                Section::make('Container')
                    ->columns(3)
                    ->schema(ContainerFields::identity()),
                Section::make('Transport')
                    ->columns(3)
                    ->schema(ContainerFields::driver()),
                Section::make('Photos')
                    ->description('Named photo slots for this container.')
                    ->columns(2)
                    ->schema(ContainerFields::photos(ImportContainer::class)),
                Section::make('Gate out & weights')
                    ->columns(3)
                    ->schema(ContainerFields::importGateOut()),
                Section::make('Inspection')
                    ->columns(3)
                    ->schema(ContainerFields::importInspection()),
                Section::make('Factory & return')
                    ->columns(3)
                    ->schema(ContainerFields::importFactoryReturn()),
                Section::make('Status')
                    ->columns(3)
                    ->schema(ContainerFields::status()),
                ContainerFields::notesSection(),
            ]);
    }
}
