<?php

/**
 * File: app/Models/ExportShipment.php
 * Responsibility: The export-process shipment header shared by its containers.
 * What it does:
 * - Stores booking-order, pickup/stuffing and sailing fields plus status and
 *   milestone state.
 * - Serves as the parent for export containers, attachments and logs.
 * How to use: `$shipment->containers`, `$shipment->advanceMilestone()`.
 * How to extend: Add export fields as columns and expose them in ExportShipmentForm.
 */

namespace App\Models;

use App\Enums\ExportMilestone;
use App\Enums\ShipmentMode;
use App\Enums\ShipmentStatus;
use App\Models\Concerns\ActsAsShipment;
use App\Models\Concerns\HasNotes;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'bl_number', 'shipment_mode', 'company_id', 'company_name_snapshot',
    'document_received_date', 'document_received_by',
    'aju_number', 'do_number', 'shipping_line', 'vessel_name',
    'voyage_number', 'port_of_loading', 'port_of_discharge', 'depot_closing_at', 'cy_closing_at',
    'pickup_depot_name', 'stuffing_date', 'stuffing_destination',
    'departure_date', 'eta_at', 'actual_arrival_at',
    'status', 'current_milestone', 'completed_at',
])]
class ExportShipment extends Model
{
    use ActsAsShipment, HasFactory, HasNotes, SoftDeletes;

    /**
     * @return class-string<ExportMilestone>
     */
    public static function milestoneEnum(): string
    {
        return ExportMilestone::class;
    }

    /**
     * The activity-log column that links entries to this shipment.
     */
    public function activityLogShipmentKey(): string
    {
        return 'export_shipment_id';
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'shipment_mode' => ShipmentMode::class,
            'status' => ShipmentStatus::class,
            'current_milestone' => ExportMilestone::class,
            'document_received_date' => 'date',
            'depot_closing_at' => 'datetime',
            'cy_closing_at' => 'datetime',
            'stuffing_date' => 'datetime',
            'departure_date' => 'date',
            'eta_at' => 'datetime',
            'actual_arrival_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Company, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * @return HasMany<ExportContainer, $this>
     */
    public function containers(): HasMany
    {
        return $this->hasMany(ExportContainer::class, 'export_shipment_id');
    }

    /**
     * @return HasMany<Attachment, $this>
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class, 'export_shipment_id');
    }

    /**
     * @return HasMany<ActivityLog, $this>
     */
    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class, 'export_shipment_id')->latest('occurred_at');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function documentReceivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'document_received_by');
    }
}
