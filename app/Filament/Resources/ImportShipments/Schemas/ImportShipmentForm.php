<?php

/**
 * File: app/Filament/Resources/ImportShipments/Schemas/ImportShipmentForm.php
 * Responsibility: Admin form for an import shipment.
 * What it does:
 * - Customer section above the tabs, then the milestone stepper on edit.
 * - Shipping Details: one field group per IMPORT.md milestone (document
 *   checking, PIB confirmation, billing/THC/DO data and sailing dates), each
 *   disabled until its milestone is reached.
 * - Containers: the shipment-level cargo fields (description of goods,
 *   packages, HS codes — unlocked with the billing response) and the loading
 *   header fields (terminal name, date of loading, loading destination) above
 *   the import container repeater; each item carries its own cargo, size,
 *   gross weight and CBM fields (all unlocked with the billing response),
 *   seeded from the shipment.
 * - Status, Notes and Activity log tabs.
 * How to use: Rendered by the import shipment create and edit pages.
 * How to extend: add a field inside the Shipping Details grid and wrap it in
 *   ShipmentFields::gated() with the milestone that unlocks it.
 */

namespace App\Filament\Resources\ImportShipments\Schemas;

use App\Enums\BillingIssuanceStatus;
use App\Enums\BillingResponse;
use App\Enums\ImportMilestone;
use App\Enums\ShipmentMode;
use App\Filament\Concerns\ContainerFields;
use App\Filament\Concerns\ShipmentFields;
use App\Models\ImportContainer;
use App\Models\ImportShipment;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class ImportShipmentForm
{
    public static function configure(Schema $schema): Schema
    {
        $enum = ImportMilestone::class;

        return $schema
            ->components([
                ShipmentFields::customerSection(),
                ShipmentFields::stepper($enum),
                Tabs::make('Import shipment')
                    ->columnSpanFull()
                    ->visibleOn('edit')
                    ->tabs([
                        Tab::make('Shipping Details')
                            ->visibleOn('edit')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        // Step 2 — Checking document.
                                        ...ShipmentFields::gated([
                                            TextInput::make('bl_number')
                                                ->label('B/L number')
                                                ->maxLength(100),
                                            Select::make('shipment_mode')
                                                ->label('Shipment mode')
                                                ->options(ShipmentMode::options()),
                                        ], $enum, ImportMilestone::CheckingDocument),
                                        // Step 3 — Draft PIB.
                                        ...ShipmentFields::gated([
                                            TextInput::make('shipping_line'),
                                        ], $enum, ImportMilestone::DraftPib),
                                        // Step 4 — Checking draft PIB to importir.
                                        ...ShipmentFields::gated([
                                            TextInput::make('vessel_name'),
                                        ], $enum, ImportMilestone::CheckingDraftPib),
                                        // Step 5 — Waiting confirmation from customer.
                                        ...ShipmentFields::gated([
                                            Toggle::make('confirmation_checklist')
                                                ->label('Confirmation checklist'),
                                        ], $enum, ImportMilestone::WaitingConfirmation),
                                        // Step 6 — Final sending PIB to custom (issuing billing).
                                        ...ShipmentFields::gated([
                                            TextInput::make('aju_number')
                                                ->label('AJU number')
                                                ->maxLength(100),
                                            TextInput::make('voyage_number')
                                                ->maxLength(100),
                                            Select::make('billing_issuance_status')
                                                ->label('Status billing')
                                                ->options(BillingIssuanceStatus::options())
                                                ->default(BillingIssuanceStatus::NotIssued),
                                        ], $enum, ImportMilestone::FinalSendingPib),
                                        // Step 7 — Process payment THC.
                                        ...ShipmentFields::gated([
                                            TextInput::make('port_of_loading'),
                                        ], $enum, ImportMilestone::ThcPayment),
                                        // Step 8 — Waiting release DO.
                                        ...ShipmentFields::gated([
                                            DatePicker::make('departure_date'),
                                        ], $enum, ImportMilestone::WaitingReleaseDo),
                                        // Step 9 — DO release.
                                        ...ShipmentFields::gated([
                                            TextInput::make('port_of_discharge'),
                                        ], $enum, ImportMilestone::DoRelease),
                                        // Step 10 — Payment billing (arrival time / ETA).
                                        ...ShipmentFields::gated([
                                            DateTimePicker::make('eta_at')
                                                ->label('Arrival time / ETA'),
                                            DateTimePicker::make('actual_arrival_at')
                                                ->label('Actual arrival'),
                                        ], $enum, ImportMilestone::BillingPayment),
                                        // Step 11 — Response billing (SPPB/AP/SPJK/SPJM).
                                        ...ShipmentFields::gated([
                                            Select::make('billing_response')
                                                ->options(BillingResponse::options()),
                                        ], $enum, ImportMilestone::ResponseBilling),
                                    ]),
                            ]),
                        ShipmentFields::containersTab(
                            $enum,
                            ImportContainer::class,
                            [
                                ...ShipmentFields::gated(ContainerFields::importIdentity(), $enum, ImportMilestone::ResponseBilling, '../../'),
                                ...ShipmentFields::gated(ContainerFields::importSize(), $enum, ImportMilestone::ResponseBilling, '../../'),
                                ...ShipmentFields::gated(ContainerFields::importWeights(), $enum, ImportMilestone::ResponseBilling, '../../'),
                                ...ShipmentFields::gated(ContainerFields::importCbm(), $enum, ImportMilestone::ResponseBilling, '../../'),
                                ...ShipmentFields::gated(ContainerFields::importCargo(), $enum, ImportMilestone::ResponseBilling, '../../'),
                                ...ShipmentFields::gated(ContainerFields::photos(ImportContainer::class), $enum, ImportMilestone::ResponseBilling, '../../'),
                                ...ShipmentFields::gated(ContainerFields::importGateOut(), $enum, ImportMilestone::GateOutCy, '../../'),
                                ...ShipmentFields::gated(ContainerFields::importDriver(), $enum, ImportMilestone::GateOutCy, '../../'),
                                Grid::make(2)
                                    ->columnSpanFull()
                                    ->schema(ShipmentFields::gated(ContainerFields::importTracking(), $enum, ImportMilestone::OnTheWayToFactory, '../../')),
                                ...ShipmentFields::gated(ContainerFields::importFactoryLoading(), $enum, ImportMilestone::ArrivedAtFactory, '../../'),
                                ...ShipmentFields::gated(ContainerFields::importReturn(), $enum, ImportMilestone::EmptyReturned, '../../'),
                            ],
                            ImportMilestone::ResponseBilling,
                            [
                                Grid::make(2)->schema([
                                    // Shipment-level cargo details, unlocked with
                                    // the billing response: these values seed each
                                    // container and stay overridable.
                                    ...ShipmentFields::gated([
                                        Textarea::make('goods_description')
                                            ->label('Description of goods')
                                            ->columnSpanFull(),
                                        TextInput::make('packages')
                                            ->maxLength(255),
                                        ShipmentFields::hsCodesField(),
                                    ], $enum, ImportMilestone::ResponseBilling),
                                ]),
                            ],
                            fn (array $data, ImportShipment $shipment): array => self::seedContainerCargo($data, $shipment),
                            [
                                Grid::make(2)->schema([
                                    // Loading data unlocks with the delivery
                                    // schedule, which is the first step after the
                                    // billing response.
                                    ...ShipmentFields::gated([
                                        TextInput::make('terminal_name')
                                            ->label('Terminal name')
                                            ->maxLength(255),
                                        DatePicker::make('loading_date')
                                            ->label('Date of loading'),
                                        Textarea::make('loading_destination')
                                            ->label('Loading destination')
                                            ->rows(2)
                                            ->columnSpanFull(),
                                    ], $enum, ImportMilestone::ContainerShippingSchedule),
                                ]),
                            ],
                        ),
                        ShipmentFields::statusTab($enum, ImportMilestone::EmptyReturned),
                        ShipmentFields::notesTab(),
                        ShipmentFields::activityLogTab(),
                    ]),
                ShipmentFields::hiddenCurrentMilestone($enum),
            ]);
    }

    /**
     * Seeds one container row from the parent shipment: the cargo fields and
     * the HS-code selection default to the shipment values and can still be
     * overridden per container. Values the container already carries win.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private static function seedContainerCargo(array $data, ImportShipment $shipment): array
    {
        if (blank($data['description_of_goods'] ?? null)) {
            $data['description_of_goods'] = $shipment->goods_description;
        }

        if (blank($data['packages'] ?? null)) {
            $data['packages'] = $shipment->packages;
        }

        if (blank($data['hsCodes'] ?? null)) {
            $data['hsCodes'] = $shipment->hsCodes()->orderBy('code')->pluck('hs_codes.id')->all();
        }

        return $data;
    }
}
