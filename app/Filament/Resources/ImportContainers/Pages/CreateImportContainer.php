<?php

/**
 * File: app/Filament/Resources/ImportContainers/Pages/CreateImportContainer.php
 * Responsibility: Creates an import container.
 * What it does:
 * - Renders the container form and keeps the create action in the page
 *   header. Containers are usually added via the shipment form's repeater.
 * - Standalone creation syncs the photo pickers and records the initial values.
 * How to use: Reached from the Import Containers resource "Create" button.
 * How to extend: Add post-create side effects in afterCreate().
 */

namespace App\Filament\Resources\ImportContainers\Pages;

use App\Filament\Resources\ImportContainers\ImportContainerResource;
use App\Models\ImportContainer;
use App\Services\ActivityLogger;
use Filament\Resources\Pages\CreateRecord;

class CreateImportContainer extends CreateRecord
{
    protected static string $resource = ImportContainerResource::class;

    protected function afterCreate(): void
    {
        $this->record->syncAttachments(
            collect(ImportContainer::photoPickers())
                ->mapWithKeys(fn (string $category, string $key): array => [
                    $category => collect($this->data[$key] ?? [])->pluck('id')->filter()->map(fn ($v): int => (int) $v)->values()->all(),
                ])
                ->all()
        );

        app(ActivityLogger::class)->recordContainerCreated($this->record);
    }

    protected function getHeaderActions(): array
    {
        return [
            $this->getCreateFormAction(),
        ];
    }
}
