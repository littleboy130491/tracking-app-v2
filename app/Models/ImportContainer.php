<?php

/**
 * File: app/Models/ImportContainer.php
 * Responsibility: A container belonging to one import shipment.
 * What it does:
 * - Tracks gate-out, weights, inspection, factory loading and depot return data.
 * - Owns its attachments; the photo pickers write through syncAttachments().
 * How to use: `$container->shipment`, `$container->attachments`.
 * How to extend: Add import container fields as columns and expose them in the
 *   import container form or the shipment form's containers repeater.
 */

namespace App\Models;

use App\Enums\ContainerStatus;
use App\Enums\FactoryLoadingStatus;
use App\Enums\InspectionStatus;
use App\Models\Concerns\ActsAsContainer;
use App\Models\Concerns\HasNotes;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'import_shipment_id', 'container_number', 'size', 'type', 'seal_number',
    'driver_name', 'license_number', 'driver_license_number',
    'gate_out_cy_at', 'gross_weight', 'gross_weight_unit', 'cbm',
    'inspection_status', 'inspected_at', 'inspection_notes',
    'factory_arrived_at', 'factory_loading_status', 'factory_loading_started_at',
    'factory_loading_finished_at', 'return_depot_name', 'empty_returned_at',
    'status', 'latest_event', 'latest_event_at', 'completed_at', 'created_by', 'updated_by',
])]
class ImportContainer extends Model
{
    use ActsAsContainer, HasFactory, HasNotes, SoftDeletes;

    /** Column on `import_containers` pointing at its shipment. */
    protected const SHIPMENT_FK = 'import_shipment_id';

    /** Column on linked rows (attachments, activity logs) pointing at this container. */
    protected const CONTAINER_FK = 'import_container_id';

    /**
     * The activity-log column that links entries to this container's shipment.
     */
    public function activityLogShipmentKey(): string
    {
        return self::SHIPMENT_FK;
    }

    /**
     * The activity-log column that links entries to this container.
     */
    public function activityLogContainerKey(): string
    {
        return self::CONTAINER_FK;
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'inspection_status' => InspectionStatus::class,
            'factory_loading_status' => FactoryLoadingStatus::class,
            'status' => ContainerStatus::class,
            'gate_out_cy_at' => 'datetime',
            'gross_weight' => 'decimal:3',
            'cbm' => 'decimal:3',
            'inspected_at' => 'datetime',
            'factory_arrived_at' => 'datetime',
            'factory_loading_started_at' => 'datetime',
            'factory_loading_finished_at' => 'datetime',
            'empty_returned_at' => 'datetime',
            'latest_event_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<ImportShipment, $this>
     */
    public function shipment(): BelongsTo
    {
        return $this->belongsTo(ImportShipment::class, 'import_shipment_id');
    }

    /**
     * @return HasMany<Attachment, $this>
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class, 'import_container_id');
    }

    /**
     * @return HasMany<ActivityLog, $this>
     */
    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class, 'import_container_id')->latest('occurred_at');
    }
}
