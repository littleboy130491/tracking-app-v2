<?php

/**
 * File: app/Filament/Resources/ImportContainers/Pages/EditImportContainer.php
 * Responsibility: Edits an import container.
 * What it does:
 * - Renders the container form; header exposes restore (on trashed records)
 *   and save.
 * - Empty cargo fields start from the parent shipment; the operator can
 *   override them per container.
 * - Standalone edits audit only the values that actually changed.
 * How to use: Reached by editing an import container.
 * How to extend: Add header actions for container-level operations.
 */

namespace App\Filament\Resources\ImportContainers\Pages;

use App\Filament\Resources\ImportContainers\ImportContainerResource;
use App\Models\Attachment;
use App\Models\ImportContainer;
use App\Services\ActivityLogger;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditImportContainer extends EditRecord
{
    protected static string $resource = ImportContainerResource::class;

    /** @var array<string, mixed> */
    protected array $auditSnapshot = [];

    protected function beforeSave(): void
    {
        $this->auditSnapshot = app(ActivityLogger::class)->containerSnapshot($this->record);
    }

    /**
     * The photo pickers are virtual (non-relationship) fields, so their
     * initial state is seeded here instead of loadStateFromRelationships —
     * Filament re-runs that hook during save and would clobber the user's
     * selection.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        foreach (ImportContainer::photoPickers() as $key => $category) {
            $data[$key] = $this->record->attachments
                ->filter(fn (Attachment $attachment): bool => $attachment->category?->value === $category)
                ->values()
                ->map->toArray()
                ->all();
        }

        return $this->seedCargoFromShipment($data);
    }

    /**
     * Fills empty cargo fields from the parent shipment, so the container
     * starts from the B/L values and the operator can override any of them.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function seedCargoFromShipment(array $data): array
    {
        $shipment = $this->record->shipment;

        if (! $shipment) {
            return $data;
        }

        $data['description_of_goods'] ??= $shipment->goods_description;
        $data['packages'] ??= $shipment->packages;

        if (blank($data['hsCodes'] ?? null)) {
            $data['hsCodes'] = ($this->record->hsCodes->isNotEmpty()
                ? $this->record->hsCodes->pluck('id')
                : $shipment->hsCodes()->orderBy('code')->pluck('hs_codes.id'))
                ->all();
        }

        return $data;
    }

    protected function afterSave(): void
    {
        $this->record->syncAttachments(
            collect(ImportContainer::photoPickers())
                ->mapWithKeys(fn (string $category, string $key): array => [
                    $category => collect($this->data[$key] ?? [])->pluck('id')->filter()->map(fn ($v): int => (int) $v)->values()->all(),
                ])
                ->all()
        );

        app(ActivityLogger::class)->recordContainerChanges($this->record, $this->auditSnapshot);
    }

    protected function getHeaderActions(): array
    {
        return [
            RestoreAction::make(),
            $this->getSaveFormAction(),
        ];
    }
}
