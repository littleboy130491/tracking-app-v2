<?php

/**
 * File: app/Models/Container.php
 * Responsibility: A container belonging to one bill of lading.
 * What it does:
 * - Tracks stuffing, inspection, factory loading, weights and depot returns.
 * - Owns its location history and attachments.
 * How to use: `$container->billOfLading`, `$container->locationUpdates`.
 * How to extend: Add container fields as columns and expose them in
 *   ContainerForm or the B/L form's containers repeater.
 */

namespace App\Models;

use App\Enums\ContainerStatus;
use App\Enums\FactoryLoadingStatus;
use App\Enums\InspectionStatus;
use App\Enums\StuffingStatus;
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
    'license_number', 'gross_weight', 'gross_weight_unit', 'cbm', 'vgm_value', 'vgm_unit',
    'gate_in_cy_at', 'gate_out_cy_at', 'inspection_status', 'inspected_at', 'inspection_notes',
    'factory_arrived_at', 'factory_loading_status', 'factory_loading_started_at',
    'factory_loading_finished_at', 'final_checked_at', 'final_checked_by', 'return_depot_name',
    'empty_returned_at', 'status', 'completed_at', 'created_by', 'updated_by',
])]
class Container extends Model
{
    use HasFactory, SoftDeletes;

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
            'final_checked_at' => 'datetime',
            'empty_returned_at' => 'datetime',
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
     * @return HasMany<ContainerLocationUpdate, $this>
     */
    public function locationUpdates(): HasMany
    {
        return $this->hasMany(ContainerLocationUpdate::class);
    }

    /**
     * @return HasMany<Attachment, $this>
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function finalCheckedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'final_checked_by');
    }
}
