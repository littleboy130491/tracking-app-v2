<?php

/**
 * File: app/Filament/Concerns/ShipmentFields.php
 * Responsibility: Form pieces shared by the export and import shipment forms.
 * What it does:
 * - Builds the customer section, the milestone stepper, the notes and
 *   activity-log tabs, and the containers repeater.
 * - Applies the milestone gate: a field is disabled (with a jump link naming
 *   the unlocking step) until its milestone is reached.
 * How to use: call the static builders from ExportShipmentForm /
 *   ImportShipmentForm, passing the model's milestone enum class.
 * How to extend: add shared blocks here; process-specific fields stay in the
 *   form class.
 */

namespace App\Filament\Concerns;

use App\Enums\BillingResponse;
use App\Enums\ShipmentStatus;
use App\Livewire\NotesPanel;
use App\Models\ActivityLog;
use App\Models\Attachment;
use App\Models\ImportShipment;
use App\Models\Role;
use App\Models\User;
use BackedEnum;
use Closure;
use Filament\Actions\Action;
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
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;

class ShipmentFields
{
    /**
     * The plain "Customer" section above the tabs: company, name snapshot and
     * document-received fields.
     */
    public static function customerSection(): Section
    {
        return Section::make()
            ->columnSpanFull()
            ->columns(2)
            ->schema([
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
                    // Staff only: customer accounts never receive documents.
                    ->relationship('documentReceivedBy', 'name', modifyQueryUsing: fn (Builder $query): Builder => $query->internal())
                    ->default(auth()->id())
                    ->disabled(fn (): bool => ! auth()->user()?->hasAnyRole(Role::PRIVILEGED))
                    ->dehydrated(fn (): bool => (bool) auth()->user()?->hasAnyRole(Role::PRIVILEGED)),
            ]);
    }

    /**
     * The interactive milestone stepper, shown above the tabs on edit.
     */
    public static function stepper(string $milestoneEnum): Placeholder
    {
        return Placeholder::make('milestone_stepper')
            ->hiddenLabel()
            ->columnSpanFull()
            ->visibleOn('edit')
            ->content(function (Get $get, ?Model $record) use ($milestoneEnum): HtmlString {
                // The create page has no record and therefore no milestone state;
                // returning an empty string (instead of an empty wrapper) keeps the
                // form from showing a blank styled block above the tabs.
                if (! $record?->exists) {
                    return new HtmlString('');
                }

                return new HtmlString(
                    view('filament.bill-of-ladings.milestone-stepper', [
                        'sequence' => $milestoneEnum::sequence(self::enumValue($get('billing_response'), BillingResponse::class)),
                        'current' => self::enumValue($get('current_milestone'), $milestoneEnum),
                        'editable' => true,
                    ])->render()
                );
            });
    }

    /**
     * Applies the milestone gate to one field: disabled until its milestone is
     * reached, with a helper text naming the step that unlocks it. Editable
     * input fields that are still empty carry the `bl-empty-field` marker so
     * the panel theme can tint them.
     */
    public static function gate(Field $field, string $milestoneEnum, BackedEnum $required, string $prefix = ''): Field
    {
        $field = $field
            ->disabled(self::locked($milestoneEnum, $required, $prefix))
            ->helperText(self::lockedHelperText($milestoneEnum, $required, $prefix));

        if ($field instanceof TextInput || $field instanceof Textarea || $field instanceof Select || $field instanceof DateTimePicker) {
            $field->extraFieldWrapperAttributes(self::emptyMarker($milestoneEnum, $required, $prefix));
        }

        return $field;
    }

    /**
     * Gates a whole field group at the same milestone.
     *
     * @param  list<Field>  $fields
     * @return list<Field>
     */
    public static function gated(array $fields, string $milestoneEnum, BackedEnum $required, string $prefix = ''): array
    {
        return array_map(
            fn (Field $field): Field => self::gate($field, $milestoneEnum, $required, $prefix),
            $fields,
        );
    }

    /**
     * The notes panel tab.
     */
    public static function notesTab(): Tab
    {
        return Tab::make('Notes')
            ->visibleOn('edit')
            ->schema([
                LivewireField::make('notes')
                    ->hiddenLabel()
                    ->dehydrated(false)
                    ->component(NotesPanel::class)
                    ->columnSpanFull(),
            ]);
    }

    /**
     * The read-only audit trail tab.
     */
    public static function activityLogTab(): Tab
    {
        return Tab::make('Activity log')
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
                        TextEntry::make('container_number')
                            ->hiddenLabel()
                            ->state(fn (ActivityLog $record): ?string => $record->linkedContainer()?->container_number)
                            ->placeholder('—'),
                        TextEntry::make('customer_summary')
                            ->hiddenLabel()
                            ->placeholder('—'),
                    ]),
            ]);
    }

    /**
     * The containers tab: optional shipment-level header components (e.g. the
     * export pickup/stuffing fields) above a repeater whose items stay open to
     * distinguish individual container records. The repeater carries the
     * `bl-containers` class so the panel theme can tint each item header.
     *
     * @param  list<Field>  $itemFields
     * @param  list<Component>  $headerComponents
     * @param  (Closure(array<string, mixed>, ImportShipment): array<string, mixed>)|null  $seedItemState
     */
    public static function containersTab(
        string $milestoneEnum,
        string $containerModel,
        array $itemFields,
        BackedEnum $unlockedAt,
        array $headerComponents = [],
        ?Closure $seedItemState = null,
    ): Tab {
        $repeater = Repeater::make('containers')
            ->relationship()
            ->defaultItems(0)
            ->extraAttributes(['class' => 'bl-containers'])
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
                ...self::photoPickerItems($containerModel, $data['id'] ?? null),
            ]);

        if ($seedItemState) {
            // Adds the item the way Filament's default add action does (append
            // an empty row, then fill it), then seeds that new row from the
            // parent shipment so its relationship fields (e.g. HS codes)
            // persist on save. Replacing the handler without the append step
            // made "Add to containers" a no-op, so keep both steps here.
            $repeater->addAction(function (Action $action) use ($seedItemState): Action {
                return $action->action(function (Repeater $component) use ($seedItemState): void {
                    $newUuid = $component->generateUuid();

                    $items = $component->getRawState();

                    if ($newUuid) {
                        $items[$newUuid] = [];
                    } else {
                        $items[] = [];
                    }

                    $component->rawState($items);

                    $key = $newUuid ?? array_key_last($items);
                    $childSchema = $component->getChildSchema($key);

                    if (! $childSchema) {
                        return;
                    }

                    $childSchema->fill();

                    $shipment = $component->getRecord();

                    if ($shipment instanceof ImportShipment) {
                        // Merge over the filled defaults so seeded cargo does
                        // not wipe per-field defaults (e.g. factory status).
                        $childSchema->rawState([
                            ...(array) $childSchema->getRawState(),
                            ...$seedItemState([], $shipment),
                        ]);
                    }

                    $component->collapsed(false, shouldMakeComponentCollapsible: false);

                    $component->callAfterStateUpdated();

                    $component->shouldPartiallyRenderAfterActionsCalled() ? $component->partiallyRender() : null;
                });
            });
        }

        return Tab::make('Containers')
            ->visibleOn('edit')
            ->schema([
                ...$headerComponents,
                self::gate($repeater->schema($itemFields), $milestoneEnum, $unlockedAt),
            ]);
    }

    /**
     * The status tab: the shipment lifecycle fields, kept out of the process
     * tabs and shown after Containers. Gated at the last milestone like the
     * rest of the form; the milestone engine maintains the same values.
     */
    public static function statusTab(string $milestoneEnum, BackedEnum $unlockedAt): Tab
    {
        return Tab::make('Status')
            ->visibleOn('edit')
            ->schema([
                ...self::gated([
                    Select::make('status')
                        ->options(ShipmentStatus::options())
                        ->default(ShipmentStatus::Draft),
                    DateTimePicker::make('completed_at')
                        ->label('Completed at'),
                ], $milestoneEnum, $unlockedAt),
            ]);
    }

    /**
     * The shipment-level HS-code multi-select, gated on the shipment form.
     * The field itself is shared with the container forms.
     */
    public static function hsCodesField(): Select
    {
        return ContainerFields::hsCodesField();
    }

    /**
     * Keeps the milestone in form state so field gating and the stepper can
     * read it, repeater items included.
     */
    public static function hiddenCurrentMilestone(string $milestoneEnum): Hidden
    {
        return Hidden::make('current_milestone')
            ->dehydrated(false)
            ->default($milestoneEnum::first()->value);
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
    public static function photoPickerItems(string $containerModel, ?int $containerId): array
    {
        $pickerKeys = array_keys($containerModel::photoPickers());

        if ($containerId === null) {
            return array_fill_keys($pickerKeys, []);
        }

        $foreignKey = (new $containerModel)->attachments()->getForeignKeyName();
        $attachments = Attachment::query()->where($foreignKey, $containerId)->get();

        return collect($containerModel::photoPickers())
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
    public static function enumValue(mixed $state, string $enum): ?BackedEnum
    {
        if ($state instanceof $enum) {
            return $state;
        }

        return is_string($state) && $state !== '' ? $enum::tryFrom($state) : null;
    }

    /**
     * Disabled-until-milestone check. `$prefix` lets container repeater items
     * read the parent shipment state via '../../'.
     */
    private static function locked(string $milestoneEnum, BackedEnum $required, string $prefix = ''): Closure
    {
        return function (Get $get) use ($milestoneEnum, $required, $prefix): bool {
            return ! $milestoneEnum::unlocked(
                self::enumValue($get($prefix.'billing_response'), BillingResponse::class),
                self::enumValue($get($prefix.'current_milestone'), $milestoneEnum),
                $required,
            );
        };
    }

    /**
     * Marks an unlocked input that has no value yet, so the admin can spot
     * missing process data at a glance. The class lands on the field wrapper;
     * the panel theme (resources/css/filament/admin/theme.css) tints it.
     */
    private static function emptyMarker(string $milestoneEnum, BackedEnum $required, string $prefix = ''): Closure
    {
        $locked = self::locked($milestoneEnum, $required, $prefix);

        return function (Get $get, mixed $state) use ($locked): array {
            if ($locked($get) || ! blank($state)) {
                return [];
            }

            return ['class' => 'bl-empty-field'];
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
    private static function lockedHelperText(string $milestoneEnum, BackedEnum $required, string $prefix = ''): Closure
    {
        $locked = self::locked($milestoneEnum, $required, $prefix);

        return function (Get $get) use ($locked, $milestoneEnum, $required, $prefix): ?HtmlString {
            if (! $locked($get)) {
                return null;
            }

            $sequence = $milestoneEnum::sequence(self::enumValue($get($prefix.'billing_response'), BillingResponse::class));
            $step = array_search($required, $sequence, true);

            $label = 'Locked until Step '.($step === false ? '?' : $step + 1).': '.$required->getLabel();

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
}
