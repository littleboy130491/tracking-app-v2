<?php

/**
 * File: app/Filament/Resources/BillOfLadings/Schemas/BillOfLadingForm.php
 * Responsibility: Admin form for the shipment header.
 * What it does:
 * - Above the tabs: a plain "Customer" section (shipment type, locked after
 *   create, customer/company relationship, document received fields), always
 *   visible; then the milestone stepper on edit only (Regress / Advance live
 *   in the edit page header).
 * - Tab "Shipping Details" (edit page only): the "Checking booking order"
 *   (Step 2) block first — AJU number, DO number, shipping line, vessel,
 *   voyage, port of loading, port of discharge, depot/CY closing times,
 *   shipment mode, B/L number — then every remaining flat field in a
 *   type-conditional list. Each field is disabled until its milestone is
 *   reached; locked fields show "Locked until Step X: name" naming the step
 *   that unlocks them.
 * - Tab "Containers" (edit page only): the containers repeater, whose items
 *   stay open to distinguish individual container records.
 * - Tab "Notes" (edit page only): the notes panel.
 * - Tab "Activity log" (edit page only): read-only table of the shipment's
 *   audit entries.
 * - reference_number is generated via a hidden field; company_name_snapshot
 *   ("Customer name") is set by the model on create and editable on edit by
 *   admin/super-admin only. The Customer dropdown is hidden on edit for
 *   non-privileged roles, who only see the read-only Customer name.
 * How to use: Rendered by the B/L create and edit pages.
 * How to extend: Add a field inside the Shipping Details tab grid and wrap it in
 *   self::gate() with the milestone that unlocks it.
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
use App\Enums\ShipmentMode;
use App\Enums\ShipmentType;
use App\Enums\StuffingStatus;
use App\Livewire\NotesPanel;
use App\Models\Attachment;
use App\Models\BillOfLading;
use App\Models\Container;
use App\Models\Role;
use App\Models\User;
use Awcodes\Curator\Components\Forms\CuratorPicker;
use Closure;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\LivewireField;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\RepeatableEntry\TableColumn;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class BillOfLadingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        Select::make('shipment_type')
                            ->options(ShipmentType::options())
                            ->default(ShipmentType::Export)
                            ->required()
                            ->live()
                            ->disabledOn('edit'),
                        Select::make('company_id')
                            ->label('Customer')
                            ->relationship('company', 'name', modifyQueryUsing: fn (Builder $query): Builder => User::scopeToAssignedCompanies($query))
                            ->required()
                            // Editable only while creating, or on edit by admin/super-admin.
                            ->hidden(fn (string $operation): bool => $operation === 'edit' && ! auth()->user()?->hasAnyRole(Role::PRIVILEGED)),
                        TextInput::make('company_name_snapshot')
                            ->label('Customer name')
                            ->disabled(fn (): bool => ! auth()->user()?->hasAnyRole(Role::PRIVILEGED))
                            ->visibleOn('edit'),
                        DatePicker::make('document_received_date')
                            ->label('Document received date')
                            ->default(today())
                            ->required(),
                        Select::make('document_received_by')
                            ->label('Document received by')
                            ->relationship('documentReceivedBy', 'name')
                            ->default(auth()->id())
                            ->disabled(fn (): bool => ! auth()->user()?->hasAnyRole(Role::PRIVILEGED))
                            ->dehydrated(fn (): bool => (bool) auth()->user()?->hasAnyRole(Role::PRIVILEGED)),
                    ]),
                Placeholder::make('milestone_stepper')
                    ->hiddenLabel()
                    ->columnSpanFull()
                    ->visibleOn('edit')
                    ->content(function (Get $get, ?BillOfLading $record): HtmlString {
                        // The create page has no record and therefore no milestone state;
                        // returning an empty string (instead of an empty wrapper) keeps the
                        // form from showing a blank styled block above the tabs.
                        if (! $record?->exists) {
                            return new HtmlString('');
                        }

                        return new HtmlString(
                            view('filament.bill-of-ladings.milestone-stepper', [
                                'sequence' => ($type = self::enumValue($get('shipment_type'), ShipmentType::class))
                                    ? ShipmentMilestone::sequence($type, self::enumValue($get('billing_response'), BillingResponse::class))
                                    : [],
                                'current' => self::enumValue($get('current_milestone'), ShipmentMilestone::class),
                                'editable' => true,
                            ])->render()
                        );
                    }),
                Tabs::make('Bill of lading')
                    ->columnSpanFull()
                    ->visibleOn('edit')
                    ->tabs([
                        Tab::make('Shipping Details')
                            ->visibleOn('edit')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        // Step 2 — Checking booking order. These unlock together.
                                        self::gate(TextInput::make('aju_number')
                                            ->label('AJU number')
                                            ->maxLength(100), ShipmentMilestone::CheckingBookingOrder),
                                        self::gate(TextInput::make('do_number')
                                            ->label('DO number')
                                            ->maxLength(100), ShipmentMilestone::CheckingBookingOrder, ShipmentMilestone::DoRelease),
                                        self::gate(TextInput::make('shipping_line'), ShipmentMilestone::CheckingBookingOrder),
                                        self::gate(TextInput::make('vessel_name'), ShipmentMilestone::CheckingBookingOrder),
                                        self::gate(TextInput::make('voyage_number')
                                            ->maxLength(100), ShipmentMilestone::CheckingBookingOrder),
                                        self::gate(TextInput::make('port_of_loading'), ShipmentMilestone::CheckingBookingOrder),
                                        self::gate(TextInput::make('port_of_discharge'), ShipmentMilestone::CheckingBookingOrder),
                                        self::gate(DateTimePicker::make('depot_closing_at')
                                            ->label('Closing time at depot')
                                            ->visible(self::visibleTo(ShipmentType::Export)), ShipmentMilestone::CheckingBookingOrder),
                                        self::gate(DateTimePicker::make('cy_closing_at')
                                            ->label('Closing time at CY')
                                            ->visible(self::visibleTo(ShipmentType::Export)), ShipmentMilestone::CheckingBookingOrder),
                                        self::gate(Select::make('shipment_mode')
                                            ->label('Shipment mode')
                                            ->options(ShipmentMode::options()), ShipmentMilestone::CheckingBookingOrder),
                                        self::gate(TextInput::make('bl_number')
                                            ->label('B/L number')
                                            ->maxLength(100), ShipmentMilestone::CheckingBookingOrder),
                                        self::gate(DatePicker::make('departure_date'), ShipmentMilestone::GateInCy, ShipmentMilestone::DraftPib),
                                        self::gate(DateTimePicker::make('eta_at')
                                            ->label('ETA'), ShipmentMilestone::GateInCy, ShipmentMilestone::DraftPib),
                                        self::gate(DateTimePicker::make('actual_arrival_at')
                                            ->label('Actual arrival'), ShipmentMilestone::GateInCy, ShipmentMilestone::DraftPib),
                                        self::gate(Select::make('draft_pib_confirmation_status')
                                            ->options(DraftPibConfirmationStatus::options())
                                            ->default(DraftPibConfirmationStatus::Pending)
                                            ->visible(self::visibleTo(ShipmentType::Import)), ShipmentMilestone::DraftPib),
                                        self::gate(DateTimePicker::make('draft_pib_confirmed_at')
                                            ->label('Confirmed at')
                                            ->visible(self::visibleTo(ShipmentType::Import)), ShipmentMilestone::DraftPib),
                                        self::gate(Textarea::make('draft_pib_confirmation_notes')
                                            ->label('Confirmation notes')
                                            ->columnSpanFull()
                                            ->visible(self::visibleTo(ShipmentType::Import)), ShipmentMilestone::DraftPib),
                                        self::gate(Select::make('billing_issuance_status')
                                            ->options(BillingIssuanceStatus::options())
                                            ->default(BillingIssuanceStatus::NotIssued)
                                            ->visible(self::visibleTo(ShipmentType::Import)), ShipmentMilestone::BillingIssued),
                                        self::gate(DateTimePicker::make('billing_issued_at')
                                            ->label('Issued at')
                                            ->visible(self::visibleTo(ShipmentType::Import)), ShipmentMilestone::BillingIssued),
                                        self::gate(Select::make('thc_payment_status')
                                            ->options(BillingPaymentStatus::options())
                                            ->default(BillingPaymentStatus::NotPaid)
                                            ->visible(self::visibleTo(ShipmentType::Import)), ShipmentMilestone::ThcPayment),
                                        self::gate(DateTimePicker::make('thc_paid_at')
                                            ->label('Paid at')
                                            ->visible(self::visibleTo(ShipmentType::Import)), ShipmentMilestone::ThcPayment),
                                        self::gate(DateTimePicker::make('do_released_at')
                                            ->label('DO released at')
                                            ->visible(self::visibleTo(ShipmentType::Import)), ShipmentMilestone::DoRelease),
                                        self::gate(Select::make('billing_payment_status')
                                            ->options(BillingPaymentStatus::options())
                                            ->default(BillingPaymentStatus::NotPaid)
                                            ->visible(self::visibleTo(ShipmentType::Import)), ShipmentMilestone::BillingPayment),
                                        self::gate(DateTimePicker::make('billing_paid_at')
                                            ->label('Paid at')
                                            ->visible(self::visibleTo(ShipmentType::Import)), ShipmentMilestone::BillingPayment),
                                        self::gate(Select::make('billing_response')
                                            ->options(BillingResponse::options())
                                            ->visible(self::visibleTo(ShipmentType::Import)), ShipmentMilestone::BillingResponseReceived),
                                        self::gate(DateTimePicker::make('billing_response_at')
                                            ->label('Response at')
                                            ->visible(self::visibleTo(ShipmentType::Import)), ShipmentMilestone::BillingResponseReceived),
                                        self::gate(Select::make('behandle_payment_status')
                                            ->options(BillingPaymentStatus::options())
                                            ->visible(self::visibleTo(ShipmentType::Import)), ShipmentMilestone::BehandlePayment),
                                        self::gate(DateTimePicker::make('behandle_paid_at')
                                            ->label('Paid at')
                                            ->visible(self::visibleTo(ShipmentType::Import)), ShipmentMilestone::BehandlePayment),
                                        self::gate(Textarea::make('goods_description')
                                            ->columnSpanFull(), ShipmentMilestone::CheckingBookingOrder, ShipmentMilestone::CheckingDocument),
                                        self::gate(TextInput::make('package_count')
                                            ->numeric(), ShipmentMilestone::CheckingBookingOrder, ShipmentMilestone::CheckingDocument),
                                        self::gate(TextInput::make('package_unit')
                                            ->maxLength(50), ShipmentMilestone::CheckingBookingOrder, ShipmentMilestone::CheckingDocument),
                                        self::gate(TextInput::make('terminal_name'), ShipmentMilestone::CheckingBookingOrder, ShipmentMilestone::CheckingDocument),
                                        self::gate(DatePicker::make('loading_date'), ShipmentMilestone::CheckingBookingOrder, ShipmentMilestone::CheckingDocument),
                                        self::gate(Textarea::make('loading_destination')
                                            ->columnSpanFull(), ShipmentMilestone::CheckingBookingOrder, ShipmentMilestone::CheckingDocument),
                                        self::gate(Select::make('hsCodes')
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
                                            ->columnSpanFull(), ShipmentMilestone::CheckingBookingOrder, ShipmentMilestone::CheckingDocument),
                                        self::gate(Select::make('status')
                                            ->options(BillOfLadingStatus::options())
                                            ->default(BillOfLadingStatus::Draft), ShipmentMilestone::FinalChecking, ShipmentMilestone::EmptyReturned),
                                        self::gate(DateTimePicker::make('completed_at')
                                            ->label('Completed at'), ShipmentMilestone::FinalChecking, ShipmentMilestone::EmptyReturned),
                                    ]),
                            ]),
                        Tab::make('Containers')
                            ->visibleOn('edit')
                            ->schema([
                                self::gate(Repeater::make('containers')
                                    ->relationship()
                                    ->defaultItems(0)
                                    ->itemLabel(function ($container): string {
                                        // Read the raw item state: the dehydrated snapshot Filament
                                        // passes as $state drops every form-field key, so it would be empty.
                                        $state = (array) $container->getRawState();

                                        return $state['container_number'] ?? 'New container';
                                    })
                                    ->collapsible()
                                    ->collapsed()
                                    ->columns(3)
                                    ->mutateRelationshipDataBeforeFillUsing(fn (array $data): array => [
                                        ...$data,
                                        ...self::photoPickerItems($data['id'] ?? null),
                                    ])
                                    ->schema(self::containerItemFields()), ShipmentMilestone::PickupEmptyContainer, ShipmentMilestone::CheckingDocument),
                            ]),
                        Tab::make('Notes')
                            ->visibleOn('edit')
                            ->schema([
                                LivewireField::make('notes')
                                    ->hiddenLabel()
                                    ->dehydrated(false)
                                    ->component(NotesPanel::class)
                                    ->columnSpanFull(),
                            ]),
                        Tab::make('Activity log')
                            ->visibleOn('edit')
                            ->schema([
                                RepeatableEntry::make('activityLogs')
                                    ->hiddenLabel()
                                    ->table([
                                        TableColumn::make('When'),
                                        TableColumn::make('Event'),
                                        TableColumn::make('By'),
                                        TableColumn::make('Container'),
                                        TableColumn::make('Summary'),
                                    ])
                                    ->schema([
                                        TextEntry::make('occurred_at')
                                            ->hiddenLabel()
                                            ->dateTime(),
                                        TextEntry::make('event')
                                            ->hiddenLabel()
                                            ->badge(),
                                        TextEntry::make('actor.name')
                                            ->hiddenLabel()
                                            ->placeholder('System'),
                                        TextEntry::make('container.container_number')
                                            ->hiddenLabel()
                                            ->placeholder('—'),
                                        TextEntry::make('customer_summary')
                                            ->hiddenLabel()
                                            ->placeholder('—'),
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
     * The flat field list inside one container repeater item. Each field reads
     * the parent B/L's milestone via '../../' so it unlocks on its own step:
     * export fills identity, transport and pickup at "Pick up empty container",
     * tracks the driver position "On the way to factory", stuffs at
     * "Stuffing / PEB & NPE", records gate-in port and date at "Checking PEB
     * & NPE", VGM at "Gate in CY" and the final check at "Final checking".
     * Import registers containers while checking documents and fills the
     * gate-out, inspection, factory and return fields at their steps.
     *
     * @return array<int, Field>
     */
    private static function containerItemFields(): array
    {
        return [
            self::gate(TextInput::make('container_number')
                ->required()
                ->distinct()
                ->maxLength(30)
                // Live-on-blur so the collapsed item header shows the number as soon as it is typed.
                ->live(onBlur: true), ShipmentMilestone::PickupEmptyContainer, ShipmentMilestone::CheckingDocument, '../../'),
            self::gate(TextInput::make('seal_number')
                ->maxLength(100), ShipmentMilestone::PickupEmptyContainer, ShipmentMilestone::CheckingDocument, '../../'),
            self::gate(Select::make('size')
                ->options(['20' => '20 ft', '40' => '40 ft', '45' => '45 ft']), ShipmentMilestone::PickupEmptyContainer, ShipmentMilestone::CheckingDocument, '../../'),
            self::gate(Select::make('type')
                ->options(['GP' => 'GP', 'HC' => 'HC', 'RF' => 'RF']), ShipmentMilestone::PickupEmptyContainer, ShipmentMilestone::CheckingDocument, '../../'),
            self::gate(TextInput::make('driver_name')
                ->maxLength(255), ShipmentMilestone::PickupEmptyContainer, ShipmentMilestone::OnTheWayToConsignee, '../../'),
            self::gate(TextInput::make('license_number')
                ->label('Truck plate number')
                ->maxLength(100), ShipmentMilestone::PickupEmptyContainer, ShipmentMilestone::OnTheWayToConsignee, '../../'),
            self::gate(TextInput::make('driver_license_number')
                ->label('Driver license number')
                ->maxLength(100), ShipmentMilestone::PickupEmptyContainer, ShipmentMilestone::OnTheWayToConsignee, '../../'),
            self::gate(TextInput::make('pickup_depot_name')
                ->label('Pick up depot')
                ->maxLength(255)
                ->visible(self::visibleTo(ShipmentType::Export, '../../')), ShipmentMilestone::PickupEmptyContainer, prefix: '../../'),
            self::gate(DateTimePicker::make('empty_picked_up_at')
                ->label('Empty picked up at')
                ->visible(self::visibleTo(ShipmentType::Export, '../../')), ShipmentMilestone::PickupEmptyContainer, prefix: '../../'),
            self::gate(DatePicker::make('stuffing_date')
                ->visible(self::visibleTo(ShipmentType::Export, '../../')), ShipmentMilestone::PickupEmptyContainer, prefix: '../../'),
            self::gate(Textarea::make('stuffing_destination')
                ->columnSpanFull()
                ->visible(self::visibleTo(ShipmentType::Export, '../../')), ShipmentMilestone::PickupEmptyContainer, prefix: '../../'),
            self::gate(TextInput::make('tracking_position')
                ->label('Tracking position')
                ->maxLength(255)
                ->visible(self::visibleTo(ShipmentType::Export, '../../')), ShipmentMilestone::OnTheWayToFactory, prefix: '../../'),
            self::gate(TextInput::make('tracking_position_url')
                ->label('Tracking position (url)')
                ->url()
                ->maxLength(500)
                ->columnSpanFull()
                ->visible(self::visibleTo(ShipmentType::Export, '../../')), ShipmentMilestone::OnTheWayToFactory, prefix: '../../'),
            ...self::photoPickers(),
            self::gate(Select::make('stuffing_status')
                ->options(StuffingStatus::options())
                ->default(StuffingStatus::NotStarted)
                ->required()
                ->visible(self::visibleTo(ShipmentType::Export, '../../')), ShipmentMilestone::StuffingPebNpe, prefix: '../../'),
            self::gate(DateTimePicker::make('stuffing_started_at')
                ->visible(self::visibleTo(ShipmentType::Export, '../../')), ShipmentMilestone::StuffingPebNpe, prefix: '../../'),
            self::gate(DateTimePicker::make('stuffing_finished_at')
                ->visible(self::visibleTo(ShipmentType::Export, '../../')), ShipmentMilestone::StuffingPebNpe, prefix: '../../'),
            self::gate(TextInput::make('gate_in_port_name')
                ->label('Gate in port')
                ->maxLength(255)
                // EXPORT.md: the gate-in port starts as the B/L's port of loading.
                ->default(fn (Get $get): ?string => BillOfLading::query()->find($get('../../id'))?->port_of_loading)
                ->visible(self::visibleTo(ShipmentType::Export, '../../')), ShipmentMilestone::CheckingPebNpe, prefix: '../../'),
            self::gate(DateTimePicker::make('gate_in_cy_at')
                ->label('Gate in CY at')
                ->visible(self::visibleTo(ShipmentType::Export, '../../')), ShipmentMilestone::CheckingPebNpe, prefix: '../../'),
            self::gate(TextInput::make('vgm_value')
                ->label('VGM (kg)')
                ->numeric()
                ->visible(self::visibleTo(ShipmentType::Export, '../../')), ShipmentMilestone::GateInCy, prefix: '../../'),
            self::gate(Checkbox::make('final_checked')
                ->label('Final checked')
                ->visible(self::visibleTo(ShipmentType::Export, '../../')), ShipmentMilestone::FinalChecking, prefix: '../../'),
            self::gate(DateTimePicker::make('final_checked_at')
                ->visible(self::visibleTo(ShipmentType::Export, '../../')), ShipmentMilestone::FinalChecking, prefix: '../../'),
            self::gate(DateTimePicker::make('gate_out_cy_at')
                ->label('Gate out CY at')
                ->visible(self::visibleTo(ShipmentType::Import, '../../')), ShipmentMilestone::GateOutCy, prefix: '../../'),
            self::gate(TextInput::make('gross_weight')
                ->numeric()
                ->visible(self::visibleTo(ShipmentType::Import, '../../')), ShipmentMilestone::GateOutCy, prefix: '../../'),
            self::gate(TextInput::make('gross_weight_unit')
                ->maxLength(20)
                ->visible(self::visibleTo(ShipmentType::Import, '../../')), ShipmentMilestone::GateOutCy, prefix: '../../'),
            self::gate(TextInput::make('cbm')
                ->numeric()
                ->visible(self::visibleTo(ShipmentType::Import, '../../')), ShipmentMilestone::GateOutCy, prefix: '../../'),
            self::gate(Select::make('inspection_status')
                ->options(InspectionStatus::options())
                ->default(InspectionStatus::NotStarted)
                ->required()
                ->visible(self::visibleTo(ShipmentType::Import, '../../')), ShipmentMilestone::Inspection, prefix: '../../'),
            self::gate(DateTimePicker::make('inspected_at')
                ->visible(self::visibleTo(ShipmentType::Import, '../../')), ShipmentMilestone::Inspection, prefix: '../../'),
            self::gate(Textarea::make('inspection_notes')
                ->columnSpanFull()
                ->visible(self::visibleTo(ShipmentType::Import, '../../')), ShipmentMilestone::Inspection, prefix: '../../'),
            self::gate(DateTimePicker::make('factory_arrived_at')
                ->visible(self::visibleTo(ShipmentType::Import, '../../')), ShipmentMilestone::ArrivedAtFactory, prefix: '../../'),
            self::gate(Select::make('factory_loading_status')
                ->options(FactoryLoadingStatus::options())
                ->default(FactoryLoadingStatus::NotStarted)
                ->required()
                ->visible(self::visibleTo(ShipmentType::Import, '../../')), ShipmentMilestone::ArrivedAtFactory, prefix: '../../'),
            self::gate(DateTimePicker::make('factory_loading_started_at')
                ->visible(self::visibleTo(ShipmentType::Import, '../../')), ShipmentMilestone::ArrivedAtFactory, prefix: '../../'),
            self::gate(DateTimePicker::make('factory_loading_finished_at')
                ->visible(self::visibleTo(ShipmentType::Import, '../../')), ShipmentMilestone::ArrivedAtFactory, prefix: '../../'),
            self::gate(TextInput::make('return_depot_name')
                ->maxLength(255)
                ->visible(self::visibleTo(ShipmentType::Import, '../../')), ShipmentMilestone::ArrivedAtFactory, prefix: '../../'),
            self::gate(DateTimePicker::make('empty_returned_at')
                ->visible(self::visibleTo(ShipmentType::Import, '../../')), ShipmentMilestone::ArrivedAtFactory, prefix: '../../'),
            self::gate(Select::make('status')
                ->options(ContainerStatus::options())
                ->default(ContainerStatus::Pending)
                ->required(), ShipmentMilestone::FinalChecking, ShipmentMilestone::EmptyReturned, '../../'),
            self::gate(DateTimePicker::make('completed_at'), ShipmentMilestone::FinalChecking, ShipmentMilestone::EmptyReturned, '../../'),
        ];
    }

    /**
     * Applies the milestone gate to one field: disabled until its milestone is
     * reached, with a helper text naming the step that unlocks it.
     */
    private static function gate(Field $field, ShipmentMilestone $required, ?ShipmentMilestone $import = null, string $prefix = ''): Field
    {
        return $field
            ->disabled(self::locked($required, $import, $prefix))
            ->helperText(self::lockedHelperText($required, $import, $prefix));
    }

    /**
     * Disabled-until-milestone check for progress fields. `$import` overrides
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
     * Per-field locked message, e.g. "Locked until Step 3: Pick up empty
     * container at depot". The step number is the milestone's position in the
     * shipment's sequence so the text matches the stepper above.
     *
     * The message is a link carrying data-bl-ms-goto="N"; clicking it scrolls
     * the page to that milestone dot and pulses it (see milestone-stepper
     * blade, which owns the delegated click handler).
     */
    private static function lockedHelperText(
        ShipmentMilestone $required,
        ?ShipmentMilestone $import = null,
        string $prefix = '',
    ): Closure {
        $locked = self::locked($required, $import, $prefix);

        return function (Get $get) use ($locked, $required, $import, $prefix): ?HtmlString {
            if (! $locked($get)) {
                return null;
            }

            $type = self::enumValue($get($prefix.'shipment_type'), ShipmentType::class) ?? ShipmentType::Export;
            $milestone = $type === ShipmentType::Import && $import ? $import : $required;
            $sequence = ShipmentMilestone::sequence($type, self::enumValue($get($prefix.'billing_response'), BillingResponse::class));
            $step = array_search($milestone, $sequence, true);

            $label = 'Locked until Step '.($step === false ? '?' : $step + 1).': '.$milestone->getLabel();

            if ($step === false) {
                return new HtmlString($label);
            }

            return new HtmlString(
                '<button type="button" class="bl-ms-goto" data-bl-ms-goto="'.($step + 1).'">'
                .'<span class="bl-ms-goto-dot">'.($step + 1).'</span> '
                .e($label)
                .'</button>'
            );
        };
    }

    /**
     * Visibility check for fields that belong to one shipment type only.
     */
    private static function visibleTo(ShipmentType $type, string $prefix = ''): Closure
    {
        return fn (Get $get): bool => self::enumValue($get($prefix.'shipment_type'), ShipmentType::class) === $type;
    }

    /**
     * The five named photo pickers per EXPORT.md, each gated at the pickup
     * step and writing its own media category.
     *
     * @return list<Field>
     */
    private static function photoPickers(): array
    {
        $labels = [
            'photo_door_items' => 'Photo — door',
            'photo_floor_items' => 'Photo — floor',
            'photo_seal_items' => 'Photo — seal',
            'photo_eir_items' => 'Photo — EIR',
            'photo_additional_items' => 'Additional photos',
        ];

        return array_map(
            fn (string $key): Field => self::gate(
                CuratorPicker::make($key)
                    ->label($labels[$key])
                    ->multiple()
                    ->dehydrated(false),
                ShipmentMilestone::PickupEmptyContainer,
                ShipmentMilestone::CheckingDocument,
                '../../',
            ),
            array_keys(Container::photoPickers()),
        );
    }

    /**
     * Media arrays for one container's photo pickers, keyed by picker state
     * key. The picker's own hydration hook turns each plain list into its
     * uuid-keyed state. Seeded through mutateRelationshipDataBeforeFillUsing
     * because a virtual (non-relationship) picker field cannot hydrate
     * itself — Filament would re-run loadStateFromRelationships during save
     * and clobber the user's selection.
     *
     * @return array<string, list<array<string, mixed>>>
     */
    private static function photoPickerItems(?int $containerId): array
    {
        if ($containerId === null) {
            return array_fill_keys(array_keys(Container::photoPickers()), []);
        }

        $attachments = Attachment::query()->where('container_id', $containerId)->get();

        return collect(Container::photoPickers())
            ->mapWithKeys(fn (string $category, string $key): array => [
                $key => $attachments
                    ->filter(fn (Attachment $attachment): bool => $attachment->category?->value === $category)
                    ->values()
                    ->map->toArray()
                    ->all(),
            ])
            ->all();
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
