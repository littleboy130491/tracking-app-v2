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
use App\Enums\StuffingStatus;
use App\Livewire\NotesPanel;
use Awcodes\Curator\Components\Forms\CuratorPicker;
use Closure;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\LivewireField;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;

class ContainerFields
{
    /**
     * Export container identity: number, size, type and seal.
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
     * Import container identity: the container number. IMPORT.md adds the
     * containers to the shipment at the response-billing step; the size is a
     * separate group because it unlocks later (upload all document).
     *
     * @return list<Field>
     */
    public static function importIdentity(): array
    {
        return [
            TextInput::make('container_number')
                ->required()
                ->distinct()
                ->maxLength(30)
                // Live-on-blur so the collapsed item header shows the number as soon as it is typed.
                ->live(onBlur: true),
        ];
    }

    /**
     * Import: container size, entered once the SPJM processing starts.
     *
     * @return list<Field>
     */
    public static function importSize(): array
    {
        return [
            Select::make('size')
                ->label('Container Size')
                ->options(['20' => '20 ft', '40' => '40 ft', '45' => '45 ft']),
        ];
    }

    /**
     * Export: driver and vehicle identity.
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
     * Import: driver name and No. license for the gate-out delivery.
     *
     * @return list<Field>
     */
    public static function importDriver(): array
    {
        return [
            TextInput::make('driver_name')
                ->maxLength(255),
            TextInput::make('license_number')
                ->label('No. License')
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
                // One photo per slot (door, floor, seal, EIR, additional).
                ->maxItems(1)
                ->dehydrated(false),
            array_keys($containerModel::photoPickers()),
        );
    }

    /**
     * Container lifecycle status and completion timestamp. Used by the import
     * container surfaces; the export container forms omit these fields.
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
     * Export: driver position tracking while on the way to the factory. The
     * callers place both fields side by side in a two-column grid.
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
                ->maxLength(500),
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
                ->label('Stuffing status at Factory')
                ->options(StuffingStatus::options())
                ->default(StuffingStatus::OnProcess->value)
                ->required(),
        ];
    }

    /**
     * Export: the port of loading used at the gate-in step. EXPORT.md: it
     * defaults to the shipment's port of loading and can be overridden. The
     * default comes from the caller (the repeater reads the parent form state,
     * the standalone form queries the selected shipment).
     *
     * @return list<Field>
     */
    public static function exportGateIn(Closure $portDefault): array
    {
        return [
            TextInput::make('port_of_loading')
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
            Toggle::make('final_checked')
                ->label('Final checked'),
            DateTimePicker::make('final_checked_at'),
        ];
    }

    /**
     * Import: gate-out datetime from the inbound terminal.
     *
     * @return list<Field>
     */
    public static function importGateOut(): array
    {
        return [
            DateTimePicker::make('gate_out_cy_at')
                ->label('Gate out CY'),
        ];
    }

    /**
     * Import: driver position tracking on the way to the factory. Mirrors the
     * export pair: a short position text plus a validated tracking URL. The
     * callers place both fields side by side in a two-column grid.
     *
     * @return list<Field>
     */
    public static function importTracking(): array
    {
        return [
            TextInput::make('tracking_position')
                ->label('Tracking position driver')
                ->maxLength(255),
            TextInput::make('tracking_position_url')
                ->label('Tracking position (url)')
                ->url()
                ->maxLength(500),
        ];
    }

    /**
     * Import: cargo details per container — description of goods, packages and
     * the HS codes this container carries. The form seeds them from the parent
     * shipment and the operator can override any of them.
     *
     * @return list<Field>
     */
    public static function importCargo(): array
    {
        return [
            Textarea::make('description_of_goods')
                ->label('Description of goods')
                ->rows(2)
                ->columnSpanFull(),
            TextInput::make('packages')
                ->maxLength(255),
            self::hsCodesField(),
        ];
    }

    /**
     * The HS-code multi-select with inline create, shared by the shipment form
     * and the container forms.
     */
    public static function hsCodesField(): Select
    {
        return Select::make('hsCodes')
            ->label('HS codes')
            ->relationship('hsCodes', 'code')
            ->multiple()
            ->searchable()
            ->preload()
            ->createOptionModalHeading('New HS code')
            ->createOptionForm([
                TextInput::make('code')
                    ->label('HS code')
                    ->required()
                    ->unique('hs_codes', 'code')
                    ->maxLength(30),
                Textarea::make('description')
                    ->rows(3),
            ])
            ->columnSpanFull();
    }

    /**
     * Import: gross weight recorded at the bahandle payment step.
     *
     * @return list<Field>
     */
    public static function importWeights(): array
    {
        return [
            TextInput::make('gross_weight')
                ->numeric(),
            TextInput::make('gross_weight_unit')
                ->maxLength(20),
        ];
    }

    /**
     * Import: CBM / measurement, recorded when the SPJM status changes to SPPB.
     *
     * @return list<Field>
     */
    public static function importCbm(): array
    {
        return [
            TextInput::make('cbm')
                ->label('CBM / measurement')
                ->numeric(),
        ];
    }

    /**
     * Import: factory loading date and status.
     *
     * @return list<Field>
     */
    public static function importFactoryLoading(): array
    {
        return [
            DateTimePicker::make('factory_loading_at')
                ->label('Loading in factory'),
            Select::make('factory_loading_status')
                ->label('Loading in factory status')
                ->options(FactoryLoadingStatus::options())
                ->default(FactoryLoadingStatus::OnProcess->value)
                ->required(),
        ];
    }

    /**
     * Import: empty container return data.
     *
     * @return list<Field>
     */
    public static function importReturn(): array
    {
        return [
            TextInput::make('return_depot_name')
                ->label('Return depot name')
                ->maxLength(255),
            DateTimePicker::make('empty_returned_at')
                ->label('Return date'),
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
