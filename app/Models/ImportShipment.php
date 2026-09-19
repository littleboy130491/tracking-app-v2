<?php

/**
 * File: app/Models/ImportShipment.php
 * Responsibility: The import-process shipment header shared by its containers.
 * What it does:
 * - Stores document-checking, PIB confirmation, billing/THC/behandle payments,
 *   DO release and sailing fields plus status and milestone state.
 * - Serves as the parent for import containers, HS codes, attachments and logs.
 * How to use: `$shipment->containers`, `$shipment->hsCodes`, `$shipment->advanceMilestone()`.
 * How to extend: Add import fields as columns and expose them in ImportShipmentForm.
 */

namespace App\Models;

use App\Enums\BillingIssuanceStatus;
use App\Enums\BillingPaymentStatus;
use App\Enums\BillingResponse;
use App\Enums\DraftPibConfirmationStatus;
use App\Enums\ImportMilestone;
use App\Enums\ShipmentMode;
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
    'bl_number', 'shipment_mode', 'company_id', 'company_name_snapshot',
    'document_received_date', 'document_received_by',
    'aju_number', 'do_number', 'shipping_line', 'vessel_name',
    'voyage_number', 'port_of_loading', 'port_of_discharge',
    'departure_date', 'eta_at', 'actual_arrival_at', 'goods_description',
    'draft_pib_confirmation_status', 'draft_pib_confirmed_at', 'draft_pib_confirmation_notes',
    'billing_issuance_status', 'billing_issued_at', 'billing_payment_status', 'billing_paid_at',
    'billing_response', 'billing_response_at', 'thc_payment_status', 'thc_paid_at',
    'behandle_payment_status', 'behandle_paid_at', 'do_released_at',
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
            'shipment_mode' => ShipmentMode::class,
            'status' => ShipmentStatus::class,
            'current_milestone' => ImportMilestone::class,
            'draft_pib_confirmation_status' => DraftPibConfirmationStatus::class,
            'billing_issuance_status' => BillingIssuanceStatus::class,
            'billing_payment_status' => BillingPaymentStatus::class,
            'billing_response' => BillingResponse::class,
            'thc_payment_status' => BillingPaymentStatus::class,
            'behandle_payment_status' => BillingPaymentStatus::class,
            'document_received_date' => 'date',
            'departure_date' => 'date',
            'eta_at' => 'datetime',
            'actual_arrival_at' => 'datetime',
            'draft_pib_confirmed_at' => 'datetime',
            'billing_issued_at' => 'datetime',
            'billing_paid_at' => 'datetime',
            'billing_response_at' => 'datetime',
            'thc_paid_at' => 'datetime',
            'behandle_paid_at' => 'datetime',
            'do_released_at' => 'datetime',
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
