<?php

/**
 * File: app/Filament/Resources/BillOfLadings/Pages/EditBillOfLading.php
 * Responsibility: Edits a bill of lading.
 * What it does:
 * - Renders the document/progress/activity-log form; header exposes Regress,
 *   Advance, restore (on trashed records) and save.
 * - `jumpToMilestone()` backs the stepper's click-to-jump buttons.
 * - Each save audits the changed B/L, nested container, HS-code and
 *   attachment-assignment values.
 * How to use: Reached by editing a shipment.
 * How to extend: Add header actions for shipment-level operations.
 */

namespace App\Filament\Resources\BillOfLadings\Pages;

use App\Enums\ShipmentMilestone;
use App\Filament\Resources\BillOfLadings\BillOfLadingResource;
use App\Models\Container;
use App\Services\ActivityLogger;
use Filament\Actions\Action;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditBillOfLading extends EditRecord
{
    protected static string $resource = BillOfLadingResource::class;

    /**
     * B/L, container and HS-code state captured immediately before Filament
     * saves relationships and the parent record.
     *
     * @var array{
     *     bill_of_lading: array<string, mixed>,
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
            ->modalHeading(fn (array $arguments): string => ($target = ShipmentMilestone::tryFrom($arguments['milestone'] ?? ''))
                ? 'Move progress to "'.$target->getLabel().'"?'
                : 'Move progress?')
            ->action(function (array $arguments): void {
                $target = ShipmentMilestone::tryFrom($arguments['milestone'] ?? '');

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
     * Media ids picked per container, keyed by container_number (unique per
     * B/L, so it also covers rows created during this save). Captured in
     * beforeSave because getState() reloads repeater items from the database
     * afterwards and wipes the virtual picker state.
     *
     * @var array<string, list<int>>
     */
    protected array $attachmentSelections = [];

    protected function beforeSave(): void
    {
        $this->auditSnapshot = app(ActivityLogger::class)->shipmentSnapshot($this->record);

        $this->attachmentSelections = collect($this->data['containers'] ?? [])
            ->mapWithKeys(fn (array $item): array => [
                (string) ($item['container_number'] ?? '') => collect($item['attachment_items'] ?? [])
                    ->pluck('id')->filter()->map(fn ($id): int => (int) $id)->values()->all(),
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
     * container_id column itself), so we point each picked media id at its
     * container using the selection captured in beforeSave.
     */
    private function syncContainerAttachments(): void
    {
        $this->record->containers()->each(function (Container $container): void {
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
