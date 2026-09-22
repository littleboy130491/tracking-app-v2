<?php

/**
 * File: app/Models/ExportContainer.php
 * Responsibility: A container belonging to one export shipment.
 * What it does:
 * - Tracks driver/tracking, stuffing, gate-in and VGM data.
 * - Owns its attachments; the photo pickers write through syncAttachments().
 * How to use: `$container->shipment`, `$container->attachments`.
 * How to extend: Add export container fields as columns and expose them in the
 *   export container form or the shipment form's containers repeater.
 */

namespace App\Models;

use App\Enums\ContainerStatus;
use App\Enums\StuffingStatus;
use App\Models\Concerns\ActsAsContainer;
use App\Models\Concerns\HasNotes;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'export_shipment_id', 'container_number', 'size', 'seal_number',
    'driver_name', 'license_number', 'driver_license_number',
    'tracking_position', 'tracking_position_url',
    'stuffing_status',
    'port_of_loading', 'gate_in_cy_at', 'vgm_value',
    'final_checked', 'final_checked_at',
    'status', 'latest_event', 'latest_event_at', 'completed_at', 'created_by', 'updated_by',
])]
class ExportContainer extends Model
{
    use ActsAsContainer, HasFactory, HasNotes, SoftDeletes;

    /** Column on `export_containers` pointing at its shipment. */
    protected const SHIPMENT_FK = 'export_shipment_id';

    /** Column on linked rows (attachments, activity logs) pointing at this container. */
    protected const CONTAINER_FK = 'export_container_id';

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
            'stuffing_status' => StuffingStatus::class,
            'status' => ContainerStatus::class,
            'vgm_value' => 'decimal:3',
            'gate_in_cy_at' => 'datetime',
            'final_checked' => 'boolean',
            'final_checked_at' => 'datetime',
            'latest_event_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<ExportShipment, $this>
     */
    public function shipment(): BelongsTo
    {
        return $this->belongsTo(ExportShipment::class, 'export_shipment_id');
    }

    /**
     * @return HasMany<Attachment, $this>
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class, 'export_container_id');
    }

    /**
     * @return HasMany<ActivityLog, $this>
     */
    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class, 'export_container_id')->latest('occurred_at');
    }
}
