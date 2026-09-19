<?php

/**
 * File: app/Filament/Resources/ImportShipments/Schemas/ImportShipmentForm.php
 * Responsibility: Admin form for an import shipment.
 * What it does:
 * - Customer section above the tabs, then the milestone stepper on edit.
 * - Shipping Details: document checking, draft PIB, billing/THC/behandle
 *   payments, DO release, billing response and sailing dates, each disabled
 *   until its milestone is reached.
 * - Containers: the import container repeater; items stay open to distinguish
 *   individual container records.
 * - Notes and Activity log tabs.
 * How to use: Rendered by the import shipment create and edit pages.
 * How to extend: add a field inside the Shipping Details grid and wrap it in
 *   ShipmentFields::gate() with the milestone that unlocks it.
 */

namespace App\Filament\Resources\ImportShipments\Schemas;

use App\Enums\BillingIssuanceStatus;
use App\Enums\BillingPaymentStatus;
use App\Enums\BillingResponse;
use App\Enums\DraftPibConfirmationStatus;
use App\Enums\ImportMilestone;
use App\Enums\ShipmentMode;
use App\Enums\ShipmentStatus;
use App\Filament\Concerns\ContainerFields;
use App\Filament\Concerns\ShipmentFields;
use App\Models\ImportContainer;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
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
                                        // Step 2 — Checking document. These unlock together.
                                        ...ShipmentFields::gated([
                                            TextInput::make('aju_number')
                                                ->label('AJU number')
                                                ->maxLength(100),
                                            TextInput::make('bl_number')
                                                ->label('B/L number')
                                                ->maxLength(100),
                                            TextInput::make('shipping_line'),
                                            TextInput::make('vessel_name'),
                                            TextInput::make('voyage_number')
                                                ->maxLength(100),
                                            TextInput::make('port_of_loading'),
                                            TextInput::make('port_of_discharge'),
                                            Select::make('shipment_mode')
                                                ->label('Shipment mode')
                                                ->options(ShipmentMode::options()),
                                            Textarea::make('goods_description')
                                                ->columnSpanFull(),
                                            ShipmentFields::hsCodesField(),
                                        ], $enum, ImportMilestone::CheckingDocument),
                                        // Draft PIB sent to the customer for confirmation.
                                        ...ShipmentFields::gated([
                                            Select::make('draft_pib_confirmation_status')
                                                ->options(DraftPibConfirmationStatus::options())
                                                ->default(DraftPibConfirmationStatus::Pending),
                                            DateTimePicker::make('draft_pib_confirmed_at')
                                                ->label('Confirmed at'),
                                            Textarea::make('draft_pib_confirmation_notes')
                                                ->label('Confirmation notes')
                                                ->columnSpanFull(),
                                        ], $enum, ImportMilestone::DraftPib),
                                        // Billing issued, THC payment, DO release.
                                        ...ShipmentFields::gated([
                                            Select::make('billing_issuance_status')
                                                ->options(BillingIssuanceStatus::options())
                                                ->default(BillingIssuanceStatus::NotIssued),
                                            DateTimePicker::make('billing_issued_at')
                                                ->label('Issued at'),
                                        ], $enum, ImportMilestone::BillingIssued),
                                        ...ShipmentFields::gated([
                                            Select::make('thc_payment_status')
                                                ->options(BillingPaymentStatus::options())
                                                ->default(BillingPaymentStatus::NotPaid),
                                            DateTimePicker::make('thc_paid_at')
                                                ->label('Paid at'),
                                        ], $enum, ImportMilestone::ThcPayment),
                                        ...ShipmentFields::gated([
                                            TextInput::make('do_number')
                                                ->label('DO number')
                                                ->maxLength(100),
                                            DateTimePicker::make('do_released_at')
                                                ->label('DO released at'),
                                        ], $enum, ImportMilestone::DoRelease),
                                        // Billing payment, customs response and behandle.
                                        ...ShipmentFields::gated([
                                            Select::make('billing_payment_status')
                                                ->options(BillingPaymentStatus::options())
                                                ->default(BillingPaymentStatus::NotPaid),
                                            DateTimePicker::make('billing_paid_at')
                                                ->label('Paid at'),
                                        ], $enum, ImportMilestone::BillingPayment),
                                        ...ShipmentFields::gated([
                                            Select::make('billing_response')
                                                ->options(BillingResponse::options()),
                                            DateTimePicker::make('billing_response_at')
                                                ->label('Response at'),
                                        ], $enum, ImportMilestone::BillingResponseReceived),
                                        ...ShipmentFields::gated([
                                            Select::make('behandle_payment_status')
                                                ->options(BillingPaymentStatus::options()),
                                            DateTimePicker::make('behandle_paid_at')
                                                ->label('Paid at'),
                                        ], $enum, ImportMilestone::BehandlePayment),
                                        // Sailing dates.
                                        ...ShipmentFields::gated([
                                            DatePicker::make('departure_date'),
                                            DateTimePicker::make('eta_at')
                                                ->label('ETA'),
                                            DateTimePicker::make('actual_arrival_at')
                                                ->label('Actual arrival'),
                                        ], $enum, ImportMilestone::DraftPib),
                                        // Completion.
                                        ...ShipmentFields::gated([
                                            Select::make('status')
                                                ->options(ShipmentStatus::options())
                                                ->default(ShipmentStatus::Draft),
                                            DateTimePicker::make('completed_at')
                                                ->label('Completed at'),
                                        ], $enum, ImportMilestone::EmptyReturned),
                                    ]),
                            ]),
                        ShipmentFields::containersTab(
                            $enum,
                            ImportContainer::class,
                            [
                                ...ShipmentFields::gated(ContainerFields::identity(), $enum, ImportMilestone::CheckingDocument, '../../'),
                                ...ShipmentFields::gated(ContainerFields::driver(), $enum, ImportMilestone::OnTheWayToConsignee, '../../'),
                                ...ShipmentFields::gated(ContainerFields::photos(ImportContainer::class), $enum, ImportMilestone::CheckingDocument, '../../'),
                                ...ShipmentFields::gated(ContainerFields::importGateOut(), $enum, ImportMilestone::GateOutCy, '../../'),
                                ...ShipmentFields::gated(ContainerFields::importInspection(), $enum, ImportMilestone::Inspection, '../../'),
                                ...ShipmentFields::gated(ContainerFields::importFactoryReturn(), $enum, ImportMilestone::ArrivedAtFactory, '../../'),
                                ...ShipmentFields::gated(ContainerFields::status(), $enum, ImportMilestone::EmptyReturned, '../../'),
                            ],
                            ImportMilestone::CheckingDocument,
                        ),
                        ShipmentFields::notesTab(),
                        ShipmentFields::activityLogTab(),
                    ]),
                ShipmentFields::hiddenReferenceNumber(),
                ShipmentFields::hiddenCurrentMilestone($enum),
            ]);
    }
}
