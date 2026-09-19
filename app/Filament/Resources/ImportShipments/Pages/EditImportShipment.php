<?php

/**
 * File: app/Filament/Resources/ImportShipments/Pages/EditImportShipment.php
 * Responsibility: Edits an import shipment.
 * What it does:
 * - Renders the customer/shipping/containers/notes/activity form; the header
 *   exposes Regress, Advance, restore (on trashed records) and save.
 * - `jumpToMilestone()` backs the stepper's click-to-jump buttons.
 * - Each save audits the changed shipment, nested container, HS-code and
 *   attachment-assignment values.
 * How to use: Reached by editing an import shipment.
 * How to extend: Add header actions for shipment-level operations.
 */

namespace App\Filament\Resources\ImportShipments\Pages;

use App\Filament\Resources\ImportShipments\ImportShipmentResource;
use App\Models\ImportContainer;
use App\Services\ActivityLogger;
use Filament\Actions\Action;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditImportShipment extends EditRecord
{
    protected static string $resource = ImportShipmentResource::class;

    /**
     * Shipment, container and HS-code state captured immediately before
     * Filament saves relationships and the parent record.
     *
     * @var array{
     *     shipment: array<string, mixed>,
     *     containers: array<int, array<string, mixed>>,
     *     hs_codes: list<string>
     * }
     */
    protected array $auditSnapshot = [];

    /**
     * Stepper click handler — resolved by name when the stepper mounts it,
     * so the jump gets the same confirmation modal as other actions.
     * Forward jumps are capped at one step; going back is unrestricted.
     */
    public function jumpToMilestoneAction(): Action
    {
        return Action::make('jumpToMilestone')
            ->requiresConfirmation()
            ->modalHeading(function (array $arguments): string {
                $enum = $this->record::milestoneEnum();
                $target = $enum::tryFrom($arguments['milestone'] ?? '');

                return $target ? 'Move progress to "'.$target->getLabel().'"?': 'Move progress?';
            })
            ->action(function (array $arguments): void {
                $enum = $this->record::milestoneEnum();
                $target = $enum::tryFrom($arguments['milestone'] ?? '');

                if (! $target) {
                    return;
                }

                $sequence = $this->record->milestoneSequence();
                $currentIndex = array_search($this->record->current_milestone, $sequence, true);
                $targetIndex = array_search($target, $sequence, true);

                if ($targetIndex === false || $currentIndex === false || $targetIndex > $currentIndex + 1) {
                    return;
                }

                $this->record->moveToMilestone($target);
                $this->record->refresh();
                $this->refreshFormData(['current_milestone']);
            });
    }

    /**
     * Photo picks per container, keyed by container_number (unique per
     * shipment, so it also covers rows created during this save) and grouped
     * by media category. Captured in beforeSave because getState() reloads
     * repeater items from the database afterwards and wipes the virtual
     * picker state.
     *
     * @var array<string, array<string, list<int>>>
     */
    protected array $attachmentSelections = [];

    protected function beforeSave(): void
    {
        $this->auditSnapshot = app(ActivityLogger::class)->shipmentSnapshot($this->record);

        $this->attachmentSelections = collect($this->data['containers'] ?? [])
            ->mapWithKeys(fn (array $item): array => [
                (string) ($item['container_number'] ?? '') => collect(ImportContainer::photoPickers())
                    ->mapWithKeys(fn (string $category, string $key): array => [
                        $category => collect($item[$key] ?? [])
                            ->pluck('id')->filter()->map(fn ($v): int => (int) $v)->values()->all(),
                    ])
                    ->all(),
            ])
            ->all();
    }

    protected function afterSave(): void
    {
        $this->syncContainerAttachments();

        app(ActivityLogger::class)->recordShipmentChanges($this->record, $this->auditSnapshot);
    }

    /**
     * The attachment picker is a virtual field (Curator cannot write the
     * container column itself), so we point each picked media id at its
     * container using the selection captured in beforeSave.
     */
    private function syncContainerAttachments(): void
    {
        $this->record->containers()->each(function (ImportContainer $container): void {
            $container->syncAttachments(
                $this->attachmentSelections[(string) $container->container_number] ?? []
            );
        });

        // Re-seed the repeater so the pickers show the synced selection.
        $this->refreshFormData(['containers']);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('regressMilestone')
                ->label('Regress')
                ->icon(Heroicon::ArrowLeft)
                ->color('gray')
                ->requiresConfirmation()
                ->visible(fn (): bool => $this->record->previousMilestone() !== null)
                ->action(function (): void {
                    $this->record->regressMilestone();
                    $this->record->refresh();
                    $this->refreshFormData(['current_milestone']);
                }),
            Action::make('advanceMilestone')
                ->label('Advance')
                ->icon(Heroicon::ArrowRight)
                ->requiresConfirmation()
                ->modalHeading(fn (): string => 'Advance to "'.$this->record->nextMilestone()?->getLabel().'"?')
                ->visible(fn (): bool => $this->record->nextMilestone() !== null)
                ->action(function (): void {
                    $this->record->advanceMilestone();
                    $this->record->refresh();
                    $this->refreshFormData(['current_milestone']);
                }),
            RestoreAction::make(),
            $this->getSaveFormAction(),
        ];
    }
}
