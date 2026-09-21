<?php

/**
 * File: app/Filament/Resources/ImportContainers/Pages/CreateImportContainer.php
 * Responsibility: Creates an import container.
 * What it does:
 * - Renders the container form and keeps the create action in the page
 *   header. Containers are usually added via the shipment form's repeater.
 * - Seeds the cargo fields from the selected shipment unless the operator
 *   typed their own values.
 * - Standalone creation syncs the photo pickers and records the initial values.
 * How to use: Reached from the Import Containers resource "Create" button.
 * How to extend: Add post-create side effects in afterCreate().
 */

namespace App\Filament\Resources\ImportContainers\Pages;

use App\Filament\Resources\ImportContainers\ImportContainerResource;
use App\Models\ImportContainer;
use App\Models\ImportShipment;
use App\Services\ActivityLogger;
use Filament\Resources\Pages\CreateRecord;

class CreateImportContainer extends CreateRecord
{
    protected static string $resource = ImportContainerResource::class;

    /** @var list<int> */
    protected array $seedHsCodes = [];

    /**
     * Seeds the container cargo fields from the selected shipment; whatever the
     * operator typed on the form wins, so every value stays overridable.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $shipmentId = $data['import_shipment_id'] ?? null;

        if (blank($shipmentId)) {
            return $data;
        }

        $shipment = ImportShipment::query()->find($shipmentId);

        if (! $shipment) {
            return $data;
        }

        $data['description_of_goods'] ??= $shipment->goods_description;
        $data['packages'] ??= $shipment->packages;
        $this->seedHsCodes = $shipment->hsCodes()->orderBy('code')->pluck('hs_codes.id')->all();

        if (blank($data['hsCodes'] ?? null)) {
            $data['hsCodes'] = $this->seedHsCodes;
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->record->syncAttachments(
            collect(ImportContainer::photoPickers())
                ->mapWithKeys(fn (string $category, string $key): array => [
                    $category => collect($this->data[$key] ?? [])->pluck('id')->filter()->map(fn ($v): int => (int) $v)->values()->all(),
                ])
                ->all()
        );

        // The relationship field does not persist on this page, so the picked
        // codes are attached explicitly; an empty pick falls back to the
        // shipment's codes, mirroring the form seeding.
        $this->record->hsCodes()->sync(
            $this->record->hsCodes()->exists()
                ? $this->record->hsCodes()->pluck('hs_codes.id')->all()
                : $this->seedHsCodes
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
