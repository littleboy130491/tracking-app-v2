<?php

/**
 * File: app/Models/Container.php
 * Responsibility: A container belonging to one bill of lading.
 * What it does:
 * - Tracks stuffing, inspection, factory loading, weights and depot returns.
 * - Owns its attachments; the driver position is a simple text column.
 * How to use: `$container->billOfLading`, `$container->attachments`.
 * How to extend: Add container fields as columns and expose them in
 *   ContainerForm or the B/L form's containers repeater.
 */

namespace App\Models;

use App\Enums\AttachmentCategory;
use App\Enums\ContainerStatus;
use App\Enums\FactoryLoadingStatus;
use App\Enums\InspectionStatus;
use App\Enums\StuffingStatus;
use App\Models\Concerns\HasNotes;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'bill_of_lading_id', 'container_number', 'size', 'type', 'seal_number',
    'pickup_depot_name', 'empty_picked_up_at', 'stuffing_date', 'stuffing_destination',
    'stuffing_status', 'stuffing_started_at', 'stuffing_finished_at', 'driver_name',
    'license_number', 'driver_license_number', 'tracking_position', 'tracking_position_url',
    'gross_weight', 'gross_weight_unit', 'cbm', 'vgm_value',
    'gate_in_port_name', 'gate_in_cy_at', 'gate_out_cy_at', 'inspection_status', 'inspected_at', 'inspection_notes',
    'factory_arrived_at', 'factory_loading_status', 'factory_loading_started_at',
    'factory_loading_finished_at', 'final_checked', 'final_checked_at', 'return_depot_name',
    'empty_returned_at', 'status', 'latest_event', 'latest_event_at', 'completed_at', 'created_by', 'updated_by',
])]
class Container extends Model
{
    use HasFactory, HasNotes, SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'stuffing_status' => StuffingStatus::class,
            'inspection_status' => InspectionStatus::class,
            'factory_loading_status' => FactoryLoadingStatus::class,
            'status' => ContainerStatus::class,
            'empty_picked_up_at' => 'datetime',
            'stuffing_date' => 'date',
            'stuffing_started_at' => 'datetime',
            'stuffing_finished_at' => 'datetime',
            'gross_weight' => 'decimal:3',
            'cbm' => 'decimal:3',
            'vgm_value' => 'decimal:3',
            'gate_in_cy_at' => 'datetime',
            'gate_out_cy_at' => 'datetime',
            'inspected_at' => 'datetime',
            'factory_arrived_at' => 'datetime',
            'factory_loading_started_at' => 'datetime',
            'factory_loading_finished_at' => 'datetime',
            'final_checked' => 'boolean',
            'final_checked_at' => 'datetime',
            'empty_returned_at' => 'datetime',
            'latest_event_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<BillOfLading, $this>
     */
    public function billOfLading(): BelongsTo
    {
        return $this->belongsTo(BillOfLading::class);
    }

    /**
     * @return HasMany<Attachment, $this>
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }

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
     * picker, and detach anything no longer picked. Curator's picker cannot
     * write the container_id column itself, so the Filament pages call this
     * after the form saves.
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
            ->where('container_id', $this->getKey())
            ->when($pickedIds->isNotEmpty(), fn ($query) => $query->whereNotIn('id', $pickedIds))
            ->update(['container_id' => null, 'category' => null]);

        foreach ($photosByCategory as $category => $mediaIds) {
            $ids = array_values(array_filter(array_map('intval', (array) $mediaIds)));

            if ($ids === []) {
                continue;
            }

            Attachment::query()->whereIn('id', $ids)->update([
                'container_id' => $this->getKey(),
                'bill_of_lading_id' => $this->bill_of_lading_id,
                'category' => $category,
            ]);

            Attachment::query()
                ->whereIn('id', $ids)
                ->whereNull('uploaded_by')
                ->update(['uploaded_by' => auth()->id()]);
        }
    }
}
