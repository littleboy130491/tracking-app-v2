<?php

/**
 * File: app/Filament/Resources/BillOfLadings/Schemas/BillOfLadingForm.php
 * Responsibility: Admin form for the shipment header.
 * What it does:
 * - Tab 1 "Document": AJU number, B/L number, shipment type, customer
 *   (company) relationship.
 * - Tab 2 "Progress": the current milestone with advance/regress actions on
 *   top, then every remaining field in one collapsible, type-conditional
 *   section per milestone. A section is disabled until its milestone is
 *   reached; disabled fields keep their values but cannot be edited.
 * - reference_number is generated via a hidden field; company_name_snapshot
 *   is set by the model on create and editable on edit by admin/super-admin
 *   only.
 * How to use: Rendered by the B/L create and edit pages.
 * How to extend: Add a tab per process step, or a collapsible section inside
 *   the Event tab.
 */

namespace App\Filament\Resources\BillOfLadings\Schemas;

use App\Enums\BillingIssuanceStatus;
use App\Enums\BillingPaymentStatus;
use App\Enums\BillingResponse;
use App\Enums\BillOfLadingStatus;
use App\Enums\ContainerStatus;
use App\Enums\DraftPibConfirmationStatus;
use App\Enums\FactoryLoadingStatus;
use App\Enums\InspectionStatus;
use App\Enums\ShipmentMilestone;
use App\Enums\ShipmentType;
use App\Enums\StuffingStatus;
use App\Models\BillOfLading;
use App\Models\Role;
use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;

class BillOfLadingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Bill of lading')
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('Document')
                            ->schema([
                                Section::make()
                                    ->columns(2)
                                    ->schema([
                                        TextInput::make('aju_number')
                                            ->label('AJU number')
                                            ->maxLength(100),
                                        TextInput::make('bl_number')
                                            ->label('B/L number')
                                            ->maxLength(100),
                                        Select::make('shipment_type')
                                            ->options(ShipmentType::options())
                                            ->default(ShipmentType::Export)
                                            ->required()
                                            ->live(),
                                        Select::make('company_id')
                                            ->label('Customer')
                                            ->relationship('company', 'name')
                                            ->required(),
                                        TextInput::make('company_name_snapshot')
                                            ->label('Customer name (snapshot)')
                                            ->disabled(fn (): bool => ! auth()->user()?->hasAnyRole(Role::PRIVILEGED))
                                            ->visibleOn('edit'),
                                    ]),
                            ]),
                        Tab::make('Progress')
                            ->schema([
                                Section::make('Current progress')
                                    ->description('Progress sections unlock as the shipment reaches each milestone.')
                                    ->headerActions([
                                        Action::make('regressMilestone')
                                            ->label('Regress')
                                            ->icon(Heroicon::ArrowLeft)
                                            ->color('gray')
                                            ->requiresConfirmation()
                                            ->visible(fn (Get $get, ?BillOfLading $record): bool => (bool) $record?->exists
                                                && self::neighborMilestone($get, 'previous') !== null)
                                            ->action(function (Set $set, ?BillOfLading $record): void {
                                                $record?->regressMilestone();
                                                $record?->refresh();
                                                $set('current_milestone', $record?->current_milestone?->value);
                                            }),
                                        Action::make('advanceMilestone')
                                            ->label(fn (Get $get): string => ($next = self::neighborMilestone($get, 'next'))
                                                ? 'Advance to '.$next->getLabel()
                                                : 'Advance')
                                            ->icon(Heroicon::ArrowRight)
                                            ->requiresConfirmation()
                                            ->modalHeading(fn (Get $get): string => 'Advance to "'.self::neighborMilestone($get, 'next')?->getLabel().'"?')
                                            ->visible(fn (Get $get, ?BillOfLading $record): bool => (bool) $record?->exists
                                                && self::neighborMilestone($get, 'next') !== null)
                                            ->action(function (Set $set, ?BillOfLading $record): void {
                                                $record?->advanceMilestone();
                                                $record?->refresh();
                                                $set('current_milestone', $record?->current_milestone?->value);
                                            }),
                                    ])
                                    ->schema([
                                        Placeholder::make('current_progress')
                                            ->label('Current milestone')
                                            ->content(fn (Get $get): string => self::currentProgressLabel($get)),
                                    ]),
                                Section::make('Booking order')
                                    ->collapsible()
                                    ->visible(fn (Get $get): bool => self::enumValue($get('shipment_type'), ShipmentType::class) === ShipmentType::Export)
                                    ->disabled(self::locked(ShipmentMilestone::CheckingBookingOrder))
                                    ->schema([
                                        TextInput::make('do_number')
                                            ->label('DO number')
                                            ->maxLength(100),
                                        DateTimePicker::make('depot_closing_at')
                                            ->label('Depot closing'),
                                        DateTimePicker::make('cy_closing_at')
                                            ->label('CY closing'),
                                    ]),
                                Section::make('Vessel & schedule')
                                    ->collapsible()
                                    ->disabled(self::locked(ShipmentMilestone::CheckingBookingOrder, ShipmentMilestone::DraftPib))
                                    ->schema([
                                        TextInput::make('shipping_line'),
                                        TextInput::make('vessel_name'),
                                        TextInput::make('voyage_number')
                                            ->maxLength(100),
                                        TextInput::make('port_of_loading'),
                                        TextInput::make('port_of_discharge'),
                                        DatePicker::make('departure_date'),
                                        DateTimePicker::make('eta_at')
                                            ->label('ETA'),
                                        DateTimePicker::make('actual_arrival_at')
                                            ->label('Actual arrival'),
                                    ]),
                                Section::make('Draft PIB')
                                    ->collapsible()
                                    ->visible(fn (Get $get): bool => self::enumValue($get('shipment_type'), ShipmentType::class) === ShipmentType::Import)
                                    ->disabled(self::locked(ShipmentMilestone::DraftPib))
                                    ->schema([
                                        Select::make('draft_pib_confirmation_status')
                                            ->options(DraftPibConfirmationStatus::options())
                                            ->default(DraftPibConfirmationStatus::Pending),
                                        DateTimePicker::make('draft_pib_confirmed_at')
                                            ->label('Confirmed at'),
                                        Textarea::make('draft_pib_confirmation_notes')
                                            ->label('Confirmation notes')
                                            ->columnSpanFull(),
                                    ]),
                                Section::make('Billing issuance')
                                    ->collapsible()
                                    ->visible(fn (Get $get): bool => self::enumValue($get('shipment_type'), ShipmentType::class) === ShipmentType::Import)
                                    ->disabled(self::locked(ShipmentMilestone::BillingIssued))
                                    ->schema([
                                        Select::make('billing_issuance_status')
                                            ->options(BillingIssuanceStatus::options())
                                            ->default(BillingIssuanceStatus::NotIssued),
                                        DateTimePicker::make('billing_issued_at')
                                            ->label('Issued at'),
                                    ]),
                                Section::make('THC payment')
                                    ->collapsible()
                                    ->visible(fn (Get $get): bool => self::enumValue($get('shipment_type'), ShipmentType::class) === ShipmentType::Import)
                                    ->disabled(self::locked(ShipmentMilestone::ThcPayment))
                                    ->schema([
                                        Select::make('thc_payment_status')
                                            ->options(BillingPaymentStatus::options())
                                            ->default(BillingPaymentStatus::NotPaid),
                                        DateTimePicker::make('thc_paid_at')
                                            ->label('Paid at'),
                                    ]),
                                Section::make('DO release')
                                    ->collapsible()
                                    ->visible(fn (Get $get): bool => self::enumValue($get('shipment_type'), ShipmentType::class) === ShipmentType::Import)
                                    ->disabled(self::locked(ShipmentMilestone::DoRelease))
                                    ->schema([
                                        DateTimePicker::make('do_released_at')
                                            ->label('DO released at'),
                                    ]),
                                Section::make('Billing payment')
                                    ->collapsible()
                                    ->visible(fn (Get $get): bool => self::enumValue($get('shipment_type'), ShipmentType::class) === ShipmentType::Import)
                                    ->disabled(self::locked(ShipmentMilestone::BillingPayment))
                                    ->schema([
                                        Select::make('billing_payment_status')
                                            ->options(BillingPaymentStatus::options())
                                            ->default(BillingPaymentStatus::NotPaid),
                                        DateTimePicker::make('billing_paid_at')
                                            ->label('Paid at'),
                                    ]),
                                Section::make('Billing response')
                                    ->collapsible()
                                    ->visible(fn (Get $get): bool => self::enumValue($get('shipment_type'), ShipmentType::class) === ShipmentType::Import)
                                    ->disabled(self::locked(ShipmentMilestone::BillingResponseReceived))
                                    ->schema([
                                        Select::make('billing_response')
                                            ->options(BillingResponse::options()),
                                        DateTimePicker::make('billing_response_at')
                                            ->label('Response at'),
                                    ]),
                                Section::make('Behandle payment')
                                    ->collapsible()
                                    ->visible(fn (Get $get): bool => self::enumValue($get('shipment_type'), ShipmentType::class) === ShipmentType::Import)
                                    ->disabled(self::locked(ShipmentMilestone::BehandlePayment))
                                    ->schema([
                                        Select::make('behandle_payment_status')
                                            ->options(BillingPaymentStatus::options()),
                                        DateTimePicker::make('behandle_paid_at')
                                            ->label('Paid at'),
                                    ]),
                                Section::make('Cargo & terminal')
                                    ->collapsible()
                                    ->disabled(self::locked(ShipmentMilestone::CheckingBookingOrder, ShipmentMilestone::CheckingDocument))
                                    ->schema([
                                        Textarea::make('goods_description')
                                            ->columnSpanFull(),
                                        TextInput::make('package_count')
                                            ->numeric(),
                                        TextInput::make('package_unit')
                                            ->maxLength(50),
                                        TextInput::make('terminal_name'),
                                        DatePicker::make('loading_date'),
                                        Textarea::make('loading_destination')
                                            ->columnSpanFull(),
                                        Select::make('hsCodes')
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
                                            ->columnSpanFull(),
                                    ]),
                                Section::make('Status')
                                    ->collapsible()
                                    ->disabled(self::locked(ShipmentMilestone::FinalChecking, ShipmentMilestone::EmptyReturned))
                                    ->schema([
                                        Select::make('status')
                                            ->options(BillOfLadingStatus::options())
                                            ->default(BillOfLadingStatus::Draft),
                                        DateTimePicker::make('completed_at')
                                            ->label('Completed at'),
                                    ]),
                            ]),
                        Tab::make('Containers')
                            ->schema([
                                Repeater::make('containers')
                                    ->relationship()
                                    ->addable(self::unlockedGate(ShipmentMilestone::CheckingBookingOrder, ShipmentMilestone::CheckingDocument))
                                    ->defaultItems(0)
                                    ->collapsible()
                                    ->collapsed()
                                    ->itemLabel(fn (array $state): ?string => $state['container_number'] ?? null)
                                    ->schema([
                                        Section::make('Container')
                                            ->disabled(self::locked(ShipmentMilestone::CheckingBookingOrder, ShipmentMilestone::CheckingDocument, '../../'))
                                            ->columns(3)
                                            ->schema([
                                                TextInput::make('container_number')
                                                    ->required()
                                                    ->distinct()
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
                                            ->disabled(self::locked(ShipmentMilestone::OnTheWayToFactory, ShipmentMilestone::OnTheWayToConsignee, '../../'))
                                            ->schema([
                                                TextInput::make('driver_name')
                                                    ->maxLength(255),
                                                TextInput::make('license_number')
                                                    ->label('Truck plate number')
                                                    ->maxLength(100),
                                            ]),
                                        Section::make('Pickup & stuffing — Export')
                                            ->visible(fn (Get $get): bool => self::enumValue($get('../../shipment_type'), ShipmentType::class) === ShipmentType::Export)
                                            ->disabled(self::locked(ShipmentMilestone::PickupEmptyContainer, prefix: '../../'))
                                            ->columns(3)
                                            ->schema([
                                                TextInput::make('pickup_depot_name')
                                                    ->maxLength(255),
                                                DateTimePicker::make('empty_picked_up_at')
                                                    ->label('Empty picked up at'),
                                                DatePicker::make('stuffing_date'),
                                                Select::make('stuffing_status')
                                                    ->options(StuffingStatus::options())
                                                    ->default(StuffingStatus::NotStarted)
                                                    ->required(),
                                                DateTimePicker::make('stuffing_started_at'),
                                                DateTimePicker::make('stuffing_finished_at'),
                                                Textarea::make('stuffing_destination')
                                                    ->columnSpanFull(),
                                            ]),
                                        Section::make('Gate in & VGM — Export')
                                            ->visible(fn (Get $get): bool => self::enumValue($get('../../shipment_type'), ShipmentType::class) === ShipmentType::Export)
                                            ->disabled(self::locked(ShipmentMilestone::GateInCy, prefix: '../../'))
                                            ->columns(3)
                                            ->schema([
                                                DateTimePicker::make('gate_in_cy_at')
                                                    ->label('Gate in CY at'),
                                                TextInput::make('vgm_value')
                                                    ->label('VGM')
                                                    ->numeric(),
                                                TextInput::make('vgm_unit')
                                                    ->label('VGM unit')
                                                    ->maxLength(20),
                                            ]),
                                        Section::make('Final check — Export')
                                            ->visible(fn (Get $get): bool => self::enumValue($get('../../shipment_type'), ShipmentType::class) === ShipmentType::Export)
                                            ->disabled(self::locked(ShipmentMilestone::FinalChecking, prefix: '../../'))
                                            ->columns(3)
                                            ->schema([
                                                DateTimePicker::make('final_checked_at'),
                                                Select::make('final_checked_by')
                                                    ->label('Final checked by')
                                                    ->relationship('finalCheckedBy', 'name')
                                                    ->searchable()
                                                    ->preload(),
                                            ]),
                                        Section::make('Gate out & weights — Import')
                                            ->visible(fn (Get $get): bool => self::enumValue($get('../../shipment_type'), ShipmentType::class) === ShipmentType::Import)
                                            ->disabled(self::locked(ShipmentMilestone::GateOutCy, prefix: '../../'))
                                            ->columns(3)
                                            ->schema([
                                                DateTimePicker::make('gate_out_cy_at')
                                                    ->label('Gate out CY at'),
                                                TextInput::make('gross_weight')->numeric(),
                                                TextInput::make('gross_weight_unit')
                                                    ->maxLength(20),
                                                TextInput::make('cbm')->numeric(),
                                            ]),
                                        Section::make('Inspection — Import')
                                            ->visible(fn (Get $get): bool => self::enumValue($get('../../shipment_type'), ShipmentType::class) === ShipmentType::Import)
                                            ->disabled(self::locked(ShipmentMilestone::Inspection, prefix: '../../'))
                                            ->columns(3)
                                            ->schema([
                                                Select::make('inspection_status')
                                                    ->options(InspectionStatus::options())
                                                    ->default(InspectionStatus::NotStarted)
                                                    ->required(),
                                                DateTimePicker::make('inspected_at'),
                                                Textarea::make('inspection_notes')
                                                    ->columnSpanFull(),
                                            ]),
                                        Section::make('Factory & return — Import')
                                            ->visible(fn (Get $get): bool => self::enumValue($get('../../shipment_type'), ShipmentType::class) === ShipmentType::Import)
                                            ->disabled(self::locked(ShipmentMilestone::ArrivedAtFactory, prefix: '../../'))
                                            ->columns(3)
                                            ->schema([
                                                DateTimePicker::make('factory_arrived_at'),
                                                Select::make('factory_loading_status')
                                                    ->options(FactoryLoadingStatus::options())
                                                    ->default(FactoryLoadingStatus::NotStarted)
                                                    ->required(),
                                                DateTimePicker::make('factory_loading_started_at'),
                                                DateTimePicker::make('factory_loading_finished_at'),
                                                TextInput::make('return_depot_name')
                                                    ->maxLength(255),
                                                DateTimePicker::make('empty_returned_at'),
                                            ]),
                                        Section::make('Status')
                                            ->disabled(self::locked(ShipmentMilestone::FinalChecking, ShipmentMilestone::EmptyReturned, '../../'))
                                            ->columns(3)
                                            ->schema([
                                                Select::make('status')
                                                    ->options(ContainerStatus::options())
                                                    ->default(ContainerStatus::Pending)
                                                    ->required(),
                                                DateTimePicker::make('completed_at'),
                                            ]),
                                    ]),
                            ]),
                    ]),
                Hidden::make('reference_number')
                    ->default(fn (): string => 'BL-'.Str::upper(Str::random(8)))
                    ->unique(ignoreRecord: true),
                // Keeps the milestone in form state so section gating and the
                // advance/regress actions can read it, repeater items included.
                Hidden::make('current_milestone')
                    ->dehydrated(false)
                    ->default(ShipmentMilestone::DocumentReceived->value),
            ]);
    }

    /**
     * Disabled-until-milestone gate for progress sections. `$import` overrides
     * the required milestone when the shipment type is import; `$prefix` lets
     * container repeater items read the parent B/L state via '../../'.
     */
    private static function locked(ShipmentMilestone $required, ?ShipmentMilestone $import = null, string $prefix = ''): Closure
    {
        return function (Get $get) use ($required, $import, $prefix): bool {
            $type = self::enumValue($get($prefix.'shipment_type'), ShipmentType::class);

            if ($type === ShipmentType::Import && $import) {
                $required = $import;
            }

            return ! ShipmentMilestone::unlocked(
                $type,
                self::enumValue($get($prefix.'billing_response'), BillingResponse::class),
                self::enumValue($get($prefix.'current_milestone'), ShipmentMilestone::class),
                $required,
            );
        };
    }

    /**
     * The inverse of `locked()` — for gates like the repeater's add button.
     */
    private static function unlockedGate(ShipmentMilestone $required, ?ShipmentMilestone $import = null, string $prefix = ''): Closure
    {
        $locked = self::locked($required, $import, $prefix);

        return fn (Get $get): bool => ! $locked($get);
    }

    /**
     * The milestone after/before the current one, from form state.
     */
    private static function neighborMilestone(Get $get, string $direction): ?ShipmentMilestone
    {
        $type = self::enumValue($get('shipment_type'), ShipmentType::class);

        if (! $type) {
            return null;
        }

        $response = self::enumValue($get('billing_response'), BillingResponse::class);
        $current = self::enumValue($get('current_milestone'), ShipmentMilestone::class);

        return $direction === 'next'
            ? ShipmentMilestone::next($type, $response, $current)
            : ShipmentMilestone::previous($type, $response, $current);
    }

    /**
     * "Step 3 of 8 — Pick up empty container at depot" for the progress header.
     */
    private static function currentProgressLabel(Get $get): string
    {
        $type = self::enumValue($get('shipment_type'), ShipmentType::class);
        $milestone = self::enumValue($get('current_milestone'), ShipmentMilestone::class);

        if (! $type || ! $milestone) {
            return '—';
        }

        $sequence = ShipmentMilestone::sequence($type, self::enumValue($get('billing_response'), BillingResponse::class));
        $position = array_search($milestone, $sequence, true);

        return 'Step '.($position === false ? 1 : $position + 1).' of '.count($sequence).' — '.$milestone->getLabel();
    }

    /**
     * Form state may hold the enum instance (model cast) or its string value.
     */
    private static function enumValue(mixed $state, string $enum): ?\BackedEnum
    {
        if ($state instanceof $enum) {
            return $state;
        }

        return is_string($state) && $state !== '' ? $enum::tryFrom($state) : null;
    }
}
