<?php

/**
 * File: app/Filament/Resources/ExportShipments/Schemas/ExportShipmentForm.php
 * Responsibility: Admin form for an export shipment.
 * What it does:
 * - Customer section above the tabs, then the milestone stepper on edit.
 * - Shipping Details: the booking-order fields, disabled until their milestone
 *   is reached.
 * - Containers: the pickup/stuffing, AJU number and sailing-date header fields
 *   above the export container repeater; items stay open to distinguish
 *   individual container records.
 * - Notes and Activity log tabs.
 * How to use: Rendered by the export shipment create and edit pages.
 * How to extend: add a field inside the Shipping Details grid and wrap it in
 *   ShipmentFields::gate() with the milestone that unlocks it.
 */

namespace App\Filament\Resources\ExportShipments\Schemas;

use App\Enums\ExportMilestone;
use App\Enums\ShipmentMode;
use App\Filament\Concerns\ContainerFields;
use App\Filament\Concerns\ShipmentFields;
use App\Models\ExportContainer;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class ExportShipmentForm
{
    public static function configure(Schema $schema): Schema
    {
        $enum = ExportMilestone::class;

        return $schema
            ->components([
                ShipmentFields::customerSection(),
                ShipmentFields::stepper($enum),
                Tabs::make('Export shipment')
                    ->columnSpanFull()
                    ->visibleOn('edit')
                    ->tabs([
                        Tab::make('Shipping Details')
                            ->visibleOn('edit')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        // Step 2 — Checking booking order. These unlock together.
                                        ...ShipmentFields::gated([
                                            TextInput::make('bl_number')
                                                ->label('B/L number')
                                                ->maxLength(100),
                                            TextInput::make('do_number')
                                                ->label('DO number')
                                                ->maxLength(100),
                                            TextInput::make('shipping_line'),
                                            TextInput::make('vessel_name'),
                                            TextInput::make('voyage_number')
                                                ->maxLength(100),
                                            TextInput::make('port_of_loading'),
                                            TextInput::make('port_of_discharge'),
                                            DateTimePicker::make('depot_closing_at')
                                                ->label('Closing time at depot'),
                                            DateTimePicker::make('cy_closing_at')
                                                ->label('Closing time at CY'),
                                            Select::make('shipment_mode')
                                                ->label('Shipment mode')
                                                ->options(ShipmentMode::options()),
                                        ], $enum, ExportMilestone::CheckingBookingOrder),
                                    ]),
                            ]),
                        ShipmentFields::containersTab(
                            $enum,
                            ExportContainer::class,
                            [
                                ...ShipmentFields::gated(ContainerFields::identity(), $enum, ExportMilestone::PickupEmptyContainer, '../../'),
                                ...ShipmentFields::gated(ContainerFields::driver(), $enum, ExportMilestone::PickupEmptyContainer, '../../'),
                                ...ShipmentFields::gated(ContainerFields::photos(ExportContainer::class), $enum, ExportMilestone::PickupEmptyContainer, '../../'),
                                Grid::make(2)
                                    ->columnSpanFull()
                                    ->schema(ShipmentFields::gated(ContainerFields::exportTracking(), $enum, ExportMilestone::OnTheWayToFactory, '../../')),
                                ...ShipmentFields::gated(ContainerFields::exportStuffing(), $enum, ExportMilestone::StuffingPebNpe, '../../'),
                                ...ShipmentFields::gated(
                                    ContainerFields::exportGateIn(fn (Get $get): ?string => $get('../../port_of_loading')),
                                    $enum,
                                    ExportMilestone::CheckingPebNpe,
                                    '../../',
                                ),
                                ...ShipmentFields::gated(ContainerFields::exportVgm(), $enum, ExportMilestone::GateInCy, '../../'),
                                ...ShipmentFields::gated(ContainerFields::exportFinalCheck(), $enum, ExportMilestone::FinalChecking, '../../'),
                            ],
                            ExportMilestone::PickupEmptyContainer,
                            [
                                Grid::make(2)->schema([
                                    // Step 3 — Pick up empty container. These are shipment
                                    // header fields, shown above the container list.
                                    ...ShipmentFields::gated([
                                        TextInput::make('pickup_depot_name')
                                            ->label('Pick up depot')
                                            ->maxLength(255),
                                        DateTimePicker::make('stuffing_date'),
                                        Textarea::make('stuffing_destination')
                                            ->columnSpanFull(),
                                    ], $enum, ExportMilestone::PickupEmptyContainer),
                                ]),
                                Grid::make(2)->schema([
                                    // Step 5 — Stuffing at factory / PEB & NPE. The AJU
                                    // number exists only once the PEB is lodged, so it
                                    // unlocks here, above the container list.
                                    ...ShipmentFields::gated([
                                        TextInput::make('aju_number')
                                            ->label('AJU number')
                                            ->maxLength(100),
                                    ], $enum, ExportMilestone::StuffingPebNpe),
                                ]),
                                Grid::make(2)->schema([
                                    // Step 7 — Gate in CY. The vessel schedule is known
                                    // once the container gates in; the dates sit with
                                    // the other container-tab header fields.
                                    ...ShipmentFields::gated([
                                        DatePicker::make('departure_date'),
                                        DateTimePicker::make('eta_at')
                                            ->label('Arrival time / ETA'),
                                    ], $enum, ExportMilestone::GateInCy),
                                ]),
                            ],
                        ),
                        ShipmentFields::statusTab($enum, ExportMilestone::FinalChecking),
                        ShipmentFields::notesTab(),
                        ShipmentFields::activityLogTab(),
                    ]),
                ShipmentFields::hiddenCurrentMilestone($enum),
            ]);
    }
}
