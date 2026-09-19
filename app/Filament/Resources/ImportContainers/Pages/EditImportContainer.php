<?php

/**
 * File: app/Filament/Resources/ImportContainers/Pages/EditImportContainer.php
 * Responsibility: Edits an import container.
 * What it does:
 * - Renders the container form; header exposes restore (on trashed records)
 *   and save.
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
