<?php

/**
 * File: app/Models/Concerns/ActsAsContainer.php
 * Responsibility: Behaviour shared by the export and import container models.
 * What it does:
 * - Declares the five named photo slots and syncs Curator media to this
 *   container, one category per picker.
 * - Reads its column names from the constants each container model defines
 *   (SHIPMENT_FK, CONTAINER_FK).
 * How to use: `use ActsAsContainer;` in ExportContainer / ImportContainer.
 * How to extend: add shared container behaviour here; process-specific fields
 *   stay in the model and its form.
 */

namespace App\Models\Concerns;

use App\Enums\AttachmentCategory;
use App\Models\Attachment;

trait ActsAsContainer
{
    /**
     * The named photo slots per EXPORT.md: picker state key => the media
     * category that picker writes.
     *
     * @return array<string, string>
     */
    public static function photoPickers(): array
    {
        return [
            'photo_door_items' => AttachmentCategory::DoorPhoto->value,
            'photo_floor_items' => AttachmentCategory::FloorPhoto->value,
            'photo_seal_items' => AttachmentCategory::SealPhoto->value,
            'photo_eir_items' => AttachmentCategory::EirPhoto->value,
            'photo_additional_items' => AttachmentCategory::AdditionalPhoto->value,
        ];
    }

    /**
     * Point the given media rows at this container, one category per photo
     * picker, and detach anything no longer picked. Ownership is exclusive:
     * all four shipment/container link columns are cleared first, so picking
     * a photo that belongs to another container (or the other process) moves
     * it instead of double-linking it. Curator's picker cannot write the
     * container columns itself, so the Filament pages call this after the
     * form saves.
     *
     * @param  array<string, list<int>>  $photosByCategory  category value => media ids
     */
    public function syncAttachments(array $photosByCategory): void
    {
        $pickedIds = collect($photosByCategory)
            ->flatten()
            ->filter()
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->values();

        Attachment::query()
            ->where(static::CONTAINER_FK, $this->getKey())
            ->when($pickedIds->isNotEmpty(), fn ($query) => $query->whereNotIn('id', $pickedIds))
            ->update([
                'export_container_id' => null,
                'export_shipment_id' => null,
                'import_container_id' => null,
                'import_shipment_id' => null,
                'category' => null,
            ]);

        foreach ($photosByCategory as $category => $mediaIds) {
            $ids = array_values(array_filter(array_map('intval', (array) $mediaIds)));

            if ($ids === []) {
                continue;
            }

            Attachment::query()->whereIn('id', $ids)->update([
                'export_container_id' => null,
                'export_shipment_id' => null,
                'import_container_id' => null,
                'import_shipment_id' => null,
                static::CONTAINER_FK => $this->getKey(),
                static::SHIPMENT_FK => $this->{static::SHIPMENT_FK},
                'category' => $category,
                // A picked photo is meant for the customer portal; without
                // this every admin pick stays internal (default false) and
                // the portal photo strip stays empty in real use.
                'is_customer_visible' => true,
            ]);

            Attachment::query()
                ->whereIn('id', $ids)
                ->whereNull('uploaded_by')
                ->update(['uploaded_by' => auth()->id()]);
        }
    }
}
