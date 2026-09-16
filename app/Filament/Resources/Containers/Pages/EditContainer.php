<?php

/**
 * File: app/Filament/Resources/Containers/Pages/EditContainer.php
 * Responsibility: Edits a container.
 * What it does:
 * - Renders the container form; header exposes restore (on trashed
 *   records) and save.
 * - Standalone edits audit only the values that actually changed.
 * How to use: Reached by editing a container.
 * How to extend: Add header actions for container-level operations.
 */

namespace App\Filament\Resources\Containers\Pages;

use App\Filament\Resources\Containers\ContainerResource;
use App\Services\ActivityLogger;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditContainer extends EditRecord
{
    protected static string $resource = ContainerResource::class;

    /** @var array<string, mixed> */
    protected array $auditSnapshot = [];

    protected function beforeSave(): void
    {
        $this->auditSnapshot = app(ActivityLogger::class)->containerSnapshot($this->record);
    }

    /**
     * The attachment picker is a virtual (non-relationship) field, so its
     * initial state is seeded here instead of loadStateFromRelationships —
     * Filament re-runs that hook during save and would clobber the user's
     * selection.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['attachment_items'] = $this->record->attachments->map->toArray()->all();

        return $data;
    }

    protected function afterSave(): void
    {
        $this->record->syncAttachments(
            collect($this->data['attachment_items'] ?? [])->pluck('id')->filter()->values()->all()
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
