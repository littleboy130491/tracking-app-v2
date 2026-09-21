<?php

/**
 * File: app/Filament/Resources/ImportShipments/Schemas/ImportShipmentForm.php
 * Responsibility: Admin form for an import shipment.
 * What it does:
 * - Customer section above the tabs, then the milestone stepper on edit.
 * - Shipping Details: one field group per IMPORT.md milestone (document
 *   checking, PIB confirmation, billing/THC/DO data, sailing dates, documents
 *   and HS codes), each disabled until its milestone is reached.
 * - Containers: the import container repeater; items stay open to distinguish
 *   individual container records.
 * - Status, Notes and Activity log tabs.
 * How to use: Rendered by the import shipment create and edit pages.
 * How to extend: add a field inside the Shipping Details grid and wrap it in
 *   ShipmentFields::gated() with the milestone that unlocks it.
 */

namespace App\Filament\Resources\ImportShipments\Schemas;

use App\Enums\BillingIssuanceStatus;
use App\Enums\BillingResponse;
use App\Enums\ImportMilestone;
use App\Filament\Concerns\ContainerFields;
use App\Filament\Concerns\ShipmentFields;
use App\Models\ImportContainer;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

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
                                        ], $enum, ImportMilestone::BillingPayment),
                                        // Step 11 — Response billing (SPPB/AP/SPJK/SPJM).
                                        ...ShipmentFields::gated([
                                            Select::make('billing_response')
                                                ->options(BillingResponse::options()),
                                        ], $enum, ImportMilestone::ResponseBilling),
                                        // Informational: the SPJM response adds customs
                                        // steps after this point; the notice shows while
                                        // the response is SPJM and is not a step itself.
                                        Placeholder::make('spjm_notice')
                                            ->hiddenLabel()
                                            ->columnSpanFull()
                                            ->visible(fn (Get $get): bool => ShipmentFields::enumValue($get('billing_response'), BillingResponse::class) === BillingResponse::Spjm)
                                            ->content(new HtmlString(
                                                '<div class="rounded-lg bg-blue-50 px-4 py-3 text-sm text-blue-800 ring-1 ring-blue-200">'
                                                .'<strong>Tambahan step SPJM.</strong> Response billing is SPJM, so the additional customs steps apply after this point: '
                                                .'Upload all document &rarr; Waiting process bahandle &rarr; Payment bahandle &rarr; Container inspection &rarr; Waiting change status SPJM to SPPB.'
                                                .'</div>'
                                            )),
                                        // Step 13 — Upload all document.
                                        ...ShipmentFields::gated([
                                            Textarea::make('goods_description')
                                                ->columnSpanFull(),
                                        ], $enum, ImportMilestone::UploadAllDocument),
                                        // Step 14 — Waiting process bahandle.
                                        ...ShipmentFields::gated([
                                            ShipmentFields::hsCodesField(),
                                        ], $enum, ImportMilestone::WaitingProcessBehandle),
                                    ]),
                            ]),
                        ShipmentFields::containersTab(
                            $enum,
                            ImportContainer::class,
                            [
                                ...ShipmentFields::gated(ContainerFields::importIdentity(), $enum, ImportMilestone::ResponseBilling, '../../'),
                                ...ShipmentFields::gated(ContainerFields::importSize(), $enum, ImportMilestone::UploadAllDocument, '../../'),
                                ...ShipmentFields::gated(ContainerFields::photos(ImportContainer::class), $enum, ImportMilestone::ResponseBilling, '../../'),
                                ...ShipmentFields::gated(ContainerFields::importGateOut(), $enum, ImportMilestone::GateOutCy, '../../'),
                                ...ShipmentFields::gated(ContainerFields::importDriver(), $enum, ImportMilestone::GateOutCy, '../../'),
                                Grid::make(2)
                                    ->columnSpanFull()
                                    ->schema(ShipmentFields::gated(ContainerFields::importTracking(), $enum, ImportMilestone::OnTheWayToFactory, '../../')),
                                ...ShipmentFields::gated(ContainerFields::importWeights(), $enum, ImportMilestone::PaymentBehandle, '../../'),
                                ...ShipmentFields::gated(ContainerFields::importCbm(), $enum, ImportMilestone::WaitingChangeStatusSppb, '../../'),
                                ...ShipmentFields::gated(ContainerFields::importFactoryLoading(), $enum, ImportMilestone::ArrivedAtFactory, '../../'),
                                ...ShipmentFields::gated(ContainerFields::importReturn(), $enum, ImportMilestone::EmptyReturned, '../../'),
                            ],
                            ImportMilestone::ResponseBilling,
                        ),
                        ShipmentFields::statusTab($enum, ImportMilestone::EmptyReturned),
                        ShipmentFields::notesTab(),
                        ShipmentFields::activityLogTab(),
                    ]),
                ShipmentFields::hiddenCurrentMilestone($enum),
            ]);
    }
}
