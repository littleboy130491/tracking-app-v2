<?php

/**
 * File: app/Models/ImportShipment.php
 * Responsibility: The import-process shipment header shared by its containers.
 * What it does:
 * - Stores the IMPORT.md fields: document checking, PIB confirmation checklist,
 *   billing/THC/DO data, sailing dates and goods description, plus status and
 *   milestone state.
 * - Serves as the parent for import containers, HS codes, attachments and logs.
 * How to use: `$shipment->containers`, `$shipment->hsCodes`, `$shipment->advanceMilestone()`.
 * How to extend: Add import fields as columns and expose them in ImportShipmentForm.
 */

namespace App\Models;

use App\Enums\BillingIssuanceStatus;
use App\Enums\BillingResponse;
use App\Enums\ImportMilestone;
use App\Enums\ShipmentStatus;
use App\Enums\ShipmentType;
use App\Models\Concerns\ActsAsShipment;
use App\Models\Concerns\HasNotes;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'bl_number', 'company_id', 'company_name_snapshot',
    'document_received_date', 'document_received_by',
    'shipping_line', 'vessel_name', 'confirmation_checklist',
    'aju_number', 'voyage_number', 'billing_issuance_status',
    'port_of_loading', 'departure_date', 'port_of_discharge',
    'eta_at', 'billing_response', 'goods_description',
    'packages', 'terminal_name', 'loading_date', 'loading_destination',
    'status', 'current_milestone', 'latest_event', 'latest_event_at', 'completed_at',
    'created_by', 'updated_by',
])]
class ImportShipment extends Model
{
    use ActsAsShipment, HasFactory, HasNotes, SoftDeletes;

    /**
     * @return class-string<ImportMilestone>
     */
    public static function milestoneEnum(): string
    {
        return ImportMilestone::class;
    }

    public function shipmentType(): ShipmentType
    {
        return ShipmentType::Import;
    }

    /**
     * The activity-log column that links entries to this shipment.
     */
    public function activityLogShipmentKey(): string
    {
        return 'import_shipment_id';
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ShipmentStatus::class,
            'current_milestone' => ImportMilestone::class,
            'billing_issuance_status' => BillingIssuanceStatus::class,
            'billing_response' => BillingResponse::class,
            'confirmation_checklist' => 'boolean',
            'document_received_date' => 'date',
            'departure_date' => 'date',
            'loading_date' => 'date',
            'eta_at' => 'datetime',
            'latest_event_at' => 'datetime',
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
     * @return HasMany<ImportContainer, $this>
     */
    public function containers(): HasMany
    {
        return $this->hasMany(ImportContainer::class, 'import_shipment_id');
    }

    /**
     * @return BelongsToMany<HsCode, $this>
     */
    public function hsCodes(): BelongsToMany
    {
        return $this->belongsToMany(HsCode::class, 'import_shipment_hs_code');
    }

    /**
     * @return HasMany<Attachment, $this>
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class, 'import_shipment_id');
    }

    /**
     * @return HasMany<ActivityLog, $this>
     */
    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class, 'import_shipment_id')->latest('occurred_at');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function documentReceivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'document_received_by');
    }
}
