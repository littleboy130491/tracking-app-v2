<?php

/**
 * File: app/Filament/Resources/Containers/Schemas/ContainerForm.php
 * Responsibility: Admin form for a shipment container.
 * What it does:
 * - Hardcoded interpretation of the export/import spec at container level:
 *   pickup/stuffing, driver position tracking, gate-in, VGM and final checking
 *   are export steps; gate-out, weights, inspection, factory loading and depot
 *   return are import steps.
 *   Sections hide themselves based on the parent B/L's shipment_type.
 * - created_by/updated_by are set by the application and never typed by hand.
 * How to use: Rendered by the container create/edit pages.
 * How to extend: Add fields here after adding the column + model fillable.
 */

namespace App\Filament\Resources\Containers\Schemas;

use App\Enums\ContainerStatus;
use App\Enums\FactoryLoadingStatus;
use App\Enums\InspectionStatus;
use App\Enums\ShipmentType;
use App\Enums\StuffingStatus;
use App\Livewire\NotesPanel;
use App\Models\BillOfLading;
use App\Models\Container;
use App\Models\User;
use Awcodes\Curator\Components\Forms\CuratorPicker;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\LivewireField;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component as LivewireComponent;

class ContainerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Container')
                    ->columns(3)
                    ->schema([
                        Select::make('bill_of_lading_id')
                            ->label('Bill of lading')
                            ->relationship('billOfLading', 'reference_number', modifyQueryUsing: fn (Builder $query): Builder => User::scopeToAssignedCompanies($query, 'company_id'))
                            ->searchable()
                            ->preload()
                            ->live()
                            ->required(),
                        TextInput::make('container_number')
                            ->required()
                            ->maxLength(30),
                        TextInput::make('seal_number')
                            ->maxLength(100),
                        Select::make('size')
                            ->options(['20' => '20 ft', '40' => '40 ft', '45' => '45 ft']),
                        Select::make('type')
                            ->options(['GP' => 'GP', 'HC' => 'HC', 'RF' => 'RF']),
                    ]),
                Section::make('Transport')
                    ->columns(3)
                    ->schema([
                        TextInput::make('driver_name')->maxLength(255),
                        TextInput::make('license_number')
                            ->label('Truck plate number')
                            ->maxLength(100),
                        TextInput::make('driver_license_number')
                            ->label('Driver license number')
                            ->maxLength(100),
                        TextInput::make('tracking_position')
                            ->label('Tracking position')
                            ->maxLength(255)
                            ->visible(fn (Get $get, ?Container $record, LivewireComponent $livewire): bool => self::shipmentType($get, $record, $livewire) === ShipmentType::Export),
                        TextInput::make('tracking_position_url')
                            ->label('Tracking position (url)')
                            ->url()
                            ->maxLength(500)
                            ->visible(fn (Get $get, ?Container $record, LivewireComponent $livewire): bool => self::shipmentType($get, $record, $livewire) === ShipmentType::Export),
                    ]),
                Section::make('Photos')
                    ->description('Named photo slots for this container.')
                    ->columns(2)
                    ->schema([
                        ...array_map(
                            fn (string $key): CuratorPicker => CuratorPicker::make($key)
                                ->label([
                                    'photo_door_items' => 'Photo — door',
                                    'photo_floor_items' => 'Photo — floor',
                                    'photo_seal_items' => 'Photo — seal',
                                    'photo_eir_items' => 'Photo — EIR',
                                    'photo_additional_items' => 'Additional photos',
                                ][$key])
                                ->multiple()
                                ->dehydrated(false),
                            array_keys(Container::photoPickers()),
                        ),
                    ]),
                Section::make('Pickup & stuffing — Export')
                    ->description('Process 2/3: pick up the empty container and stuff at the factory.')
                    ->visible(fn (Get $get, ?Container $record, LivewireComponent $livewire): bool => self::shipmentType($get, $record, $livewire) === ShipmentType::Export)
                    ->columns(3)
                    ->schema([
                        TextInput::make('pickup_depot_name')->maxLength(255),
                        DateTimePicker::make('empty_picked_up_at')
                            ->label('Empty picked up at'),
                        DatePicker::make('stuffing_date'),
                        Select::make('stuffing_status')
                            ->options(StuffingStatus::options())
                            ->default(StuffingStatus::NotStarted->value)
                            ->required(),
                        DateTimePicker::make('stuffing_started_at'),
                        DateTimePicker::make('stuffing_finished_at'),
                        Textarea::make('stuffing_destination')
                            ->columnSpanFull(),
                    ]),
                Section::make('Gate in & VGM — Export')
                    ->visible(fn (Get $get, ?Container $record, LivewireComponent $livewire): bool => self::shipmentType($get, $record, $livewire) === ShipmentType::Export)
                    ->columns(3)
                    ->schema([
                        TextInput::make('gate_in_port_name')
                            ->label('Gate in port')
                            ->maxLength(255)
                            // EXPORT.md: the gate-in port starts as the B/L's port of loading.
                            ->default(fn (Get $get): ?string => BillOfLading::query()->find($get('bill_of_lading_id'))?->port_of_loading),
                        DateTimePicker::make('gate_in_cy_at')
                            ->label('Gate in CY at'),
                        TextInput::make('vgm_value')
                            ->label('VGM (kg)')
                            ->numeric(),
                    ]),
                Section::make('Final check — Export')
                    ->visible(fn (Get $get, ?Container $record, LivewireComponent $livewire): bool => self::shipmentType($get, $record, $livewire) === ShipmentType::Export)
                    ->columns(3)
                    ->schema([
                        Checkbox::make('final_checked')
                            ->label('Final checked'),
                        DateTimePicker::make('final_checked_at'),
                    ]),
                Section::make('Gate out & weights — Import')
                    ->visible(fn (Get $get, ?Container $record, LivewireComponent $livewire): bool => self::shipmentType($get, $record, $livewire) === ShipmentType::Import)
                    ->columns(3)
                    ->schema([
                        DateTimePicker::make('gate_out_cy_at')
                            ->label('Gate out CY at'),
                        TextInput::make('gross_weight')->numeric(),
                        TextInput::make('gross_weight_unit')->maxLength(20),
                        TextInput::make('cbm')->numeric(),
                    ]),
                Section::make('Inspection — Import')
                    ->visible(fn (Get $get, ?Container $record, LivewireComponent $livewire): bool => self::shipmentType($get, $record, $livewire) === ShipmentType::Import)
                    ->columns(3)
                    ->schema([
                        Select::make('inspection_status')
                            ->options(InspectionStatus::options())
                            ->default(InspectionStatus::NotStarted->value)
                            ->required(),
                        DateTimePicker::make('inspected_at'),
                        Textarea::make('inspection_notes')
                            ->columnSpanFull(),
                    ]),
                Section::make('Factory & return — Import')
                    ->visible(fn (Get $get, ?Container $record, LivewireComponent $livewire): bool => self::shipmentType($get, $record, $livewire) === ShipmentType::Import)
                    ->columns(3)
                    ->schema([
                        DateTimePicker::make('factory_arrived_at'),
                        Select::make('factory_loading_status')
                            ->options(FactoryLoadingStatus::options())
                            ->default(FactoryLoadingStatus::NotStarted->value)
                            ->required(),
                        DateTimePicker::make('factory_loading_started_at'),
                        DateTimePicker::make('factory_loading_finished_at'),
                        TextInput::make('return_depot_name')->maxLength(255),
                        DateTimePicker::make('empty_returned_at'),
                    ]),
                Section::make('Status')
                    ->columns(3)
                    ->schema([
                        Select::make('status')
                            ->options(ContainerStatus::options())
                            ->default(ContainerStatus::Pending->value)
                            ->required(),
                        DateTimePicker::make('completed_at'),
                    ]),
                Section::make('Notes')
                    ->visibleOn('edit')
                    ->schema([
                        LivewireField::make('notes')
                            ->hiddenLabel()
                            ->dehydrated(false)
                            ->component(NotesPanel::class),
                    ]),
            ]);
    }

    /**
     * The parent B/L fixes the shipment type: from the record on edit, the
     * relation-manager owner on quick-create, or the B/L select on standalone
     * create.
     */
    private static function shipmentType(Get $get, ?Container $record, LivewireComponent $livewire): ?ShipmentType
    {
        $billOfLading = $record?->billOfLading
            ?? ($livewire instanceof RelationManager ? $livewire->getOwnerRecord() : null)
            ?? BillOfLading::query()->find($get('bill_of_lading_id'));

        return $billOfLading instanceof BillOfLading ? $billOfLading->shipment_type : null;
    }
}
