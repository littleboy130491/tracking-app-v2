<?php

/**
 * File: app/Filament/Concerns/ContainerFields.php
 * Responsibility: Container form groups shared by the shipment repeaters and the container forms.
 * What it does:
 * - Returns ungated field groups (identity, driver, photos, status, export
 *   process, import process); callers apply the milestone gate where needed.
 * How to use: spread the groups into a repeater or section schema, e.g.
 *   `...ShipmentFields::gated(ContainerFields::identity(), $enum, $milestone, '../../')`.
 * How to extend: add a group per new process step; gate it in the caller.
 */

namespace App\Filament\Concerns;

use App\Enums\ContainerStatus;
use App\Enums\FactoryLoadingStatus;
use App\Enums\InspectionStatus;
use App\Enums\StuffingStatus;
use App\Livewire\NotesPanel;
use Awcodes\Curator\Components\Forms\CuratorPicker;
use Closure;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\LivewireField;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;

class ContainerFields
{
    /**
     * Container identity: number, size, type and seal.
     *
     * @return list<Field>
     */
    public static function identity(): array
    {
        return [
            TextInput::make('container_number')
                ->required()
                ->distinct()
                ->maxLength(30)
                // Live-on-blur so the collapsed item header shows the number as soon as it is typed.
                ->live(onBlur: true),
            Select::make('size')
                ->label('Container Size')
                ->options(['20' => '20 ft', '40' => '40 ft', '45' => '45 ft']),
            Select::make('type')
                ->label('Container Type')
                ->options(['GP' => 'GP', 'HC' => 'HC', 'RF' => 'RF']),
            TextInput::make('seal_number')
                ->maxLength(100),
        ];
    }

    /**
     * Driver and vehicle identity.
     *
     * @return list<Field>
     */
    public static function driver(): array
    {
        return [
            TextInput::make('driver_name')
                ->maxLength(255),
            TextInput::make('license_number')
                ->label('Vehicle / Truck Number')
                ->maxLength(100),
            TextInput::make('driver_license_number')
                ->label('Driver License Number')
                ->maxLength(100),
        ];
    }

    /**
     * The five named photo pickers, keyed per container model.
     *
     * @return list<CuratorPicker>
     */
    public static function photos(string $containerModel): array
    {
        $labels = [
            'photo_door_items' => 'Photo Door',
            'photo_floor_items' => 'Photo Floor',
            'photo_seal_items' => 'Photo Seal',
            'photo_eir_items' => 'Photo EIR',
            'photo_additional_items' => 'Additional Photos',
        ];

        return array_map(
            fn (string $key): CuratorPicker => CuratorPicker::make($key)
                ->label($labels[$key])
                ->multiple()
                ->dehydrated(false),
            array_keys($containerModel::photoPickers()),
        );
    }

    /**
     * Container lifecycle status.
     *
     * @return list<Field>
     */
    public static function status(): array
    {
        return [
            Select::make('status')
                ->options(ContainerStatus::options())
                ->default(ContainerStatus::Pending->value)
                ->required(),
            DateTimePicker::make('completed_at'),
        ];
    }

    /**
     * Export: driver position tracking while on the way to the factory.
     *
     * @return list<Field>
     */
    public static function exportTracking(): array
    {
        return [
            TextInput::make('tracking_position')
                ->label('Tracking position')
                ->maxLength(255),
            TextInput::make('tracking_position_url')
                ->label('Tracking position (url)')
                ->url()
                ->maxLength(500)
                ->columnSpanFull(),
        ];
    }

    /**
     * Export: stuffing progress at the factory.
     *
     * @return list<Field>
     */
    public static function exportStuffing(): array
    {
        return [
            Select::make('stuffing_status')
                ->options(StuffingStatus::options())
                ->default(StuffingStatus::NotStarted->value)
                ->required(),
            DateTimePicker::make('stuffing_started_at'),
            DateTimePicker::make('stuffing_finished_at'),
        ];
    }

    /**
     * Export: gate-in at the terminal. EXPORT.md: the gate-in port starts as
     * the shipment's port of loading, so the default comes from the caller
     * (repeater reads the parent state, the standalone form queries the
     * selected shipment).
     *
     * @return list<Field>
     */
    public static function exportGateIn(Closure $portDefault): array
    {
        return [
            TextInput::make('gate_in_port_name')
                ->label('Gate in port')
                ->maxLength(255)
                ->default($portDefault),
            DateTimePicker::make('gate_in_cy_at')
                ->label('Gate in CY at'),
        ];
    }

    /**
     * Export: VGM is recorded in kilograms per the export spec.
     *
     * @return list<Field>
     */
    public static function exportVgm(): array
    {
        return [
            TextInput::make('vgm_value')
                ->label('VGM (kg)')
                ->numeric(),
        ];
    }

    /**
     * Export: final checking at the last step.
     *
     * @return list<Field>
     */
    public static function exportFinalCheck(): array
    {
        return [
            Checkbox::make('final_checked')
                ->label('Final checked'),
            DateTimePicker::make('final_checked_at'),
        ];
    }

    /**
     * Import: gate out and weights.
     *
     * @return list<Field>
     */
    public static function importGateOut(): array
    {
        return [
            DateTimePicker::make('gate_out_cy_at')
                ->label('Gate out CY at'),
            TextInput::make('gross_weight')
                ->numeric(),
            TextInput::make('gross_weight_unit')
                ->maxLength(20),
            TextInput::make('cbm')
                ->numeric(),
        ];
    }

    /**
     * Import: container inspection.
     *
     * @return list<Field>
     */
    public static function importInspection(): array
    {
        return [
            Select::make('inspection_status')
                ->options(InspectionStatus::options())
                ->default(InspectionStatus::NotStarted->value)
                ->required(),
            DateTimePicker::make('inspected_at'),
            Textarea::make('inspection_notes')
                ->columnSpanFull(),
        ];
    }

    /**
     * Import: factory arrival, loading and empty return.
     *
     * @return list<Field>
     */
    public static function importFactoryReturn(): array
    {
        return [
            DateTimePicker::make('factory_arrived_at'),
            Select::make('factory_loading_status')
                ->options(FactoryLoadingStatus::options())
                ->default(FactoryLoadingStatus::NotStarted->value)
                ->required(),
            DateTimePicker::make('factory_loading_started_at'),
            DateTimePicker::make('factory_loading_finished_at'),
            TextInput::make('return_depot_name')
                ->maxLength(255),
            DateTimePicker::make('empty_returned_at'),
        ];
    }

    /**
     * The notes panel section used by the standalone container forms.
     */
    public static function notesSection(): Section
    {
        return Section::make('Notes')
            ->visibleOn('edit')
            ->schema([
                LivewireField::make('notes')
                    ->hiddenLabel()
                    ->dehydrated(false)
                    ->component(NotesPanel::class),
            ]);
    }
}
