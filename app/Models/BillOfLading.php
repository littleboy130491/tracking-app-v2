<?php

/**
 * File: app/Models/BillOfLading.php
 * Responsibility: The shipment header shared by all of its containers.
 * What it does:
 * - Stores shared operational fields and billing/response state.
 * - Serves as the parent for containers, HS codes, attachments and logs.
 * How to use: `$bl->containers`, `$bl->hsCodes`, `$bl->isImport()`.
 * How to extend: Add shared fields as columns and expose them in
 *   BillOfLadingForm.
 */

namespace App\Models;

use App\Enums\BillingIssuanceStatus;
use App\Enums\BillingPaymentStatus;
use App\Enums\BillingResponse;
use App\Enums\BillOfLadingStatus;
use App\Enums\DraftPibConfirmationStatus;
use App\Enums\ShipmentMilestone;
use App\Enums\ShipmentType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'reference_number', 'bl_number', 'shipment_type', 'company_id', 'company_name_snapshot',
    'aju_number', 'do_number', 'shipping_line', 'vessel_name',
    'voyage_number', 'port_of_loading', 'port_of_discharge', 'depot_closing_at', 'cy_closing_at',
    'departure_date', 'eta_at', 'actual_arrival_at', 'goods_description', 'package_count',
    'package_unit', 'terminal_name', 'loading_date', 'loading_destination',
    'draft_pib_confirmation_status', 'draft_pib_confirmed_at', 'draft_pib_confirmation_notes',
    'billing_issuance_status', 'billing_issued_at', 'billing_payment_status', 'billing_paid_at',
    'billing_response', 'billing_response_at', 'thc_payment_status', 'thc_paid_at',
    'behandle_payment_status', 'behandle_paid_at', 'do_released_at', 'status', 'current_milestone',
    'completed_at', 'created_by', 'updated_by',
])]
class BillOfLading extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The company name is captured once on create so a later rename does not
     * rewrite the shipment's history. A shipment-type change resets the
     * milestone when the stored one is not part of the new type's sequence.
     */
    protected static function booted(): void
    {
        static::creating(function (BillOfLading $billOfLading): void {
            $billOfLading->company_name_snapshot ??= $billOfLading->company?->name;
        });

        static::saving(function (BillOfLading $billOfLading): void {
            if ($billOfLading->isDirty('shipment_type') && $billOfLading->shipment_type
                && ! in_array($billOfLading->current_milestone, ShipmentMilestone::sequence($billOfLading->shipment_type, $billOfLading->billing_response), true)) {
                $billOfLading->current_milestone = ShipmentMilestone::first($billOfLading->shipment_type);
            }
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'shipment_type' => ShipmentType::class,
            'status' => BillOfLadingStatus::class,
            'current_milestone' => ShipmentMilestone::class,
            'draft_pib_confirmation_status' => DraftPibConfirmationStatus::class,
            'billing_issuance_status' => BillingIssuanceStatus::class,
            'billing_payment_status' => BillingPaymentStatus::class,
            'billing_response' => BillingResponse::class,
            'thc_payment_status' => BillingPaymentStatus::class,
            'behandle_payment_status' => BillingPaymentStatus::class,
            'depot_closing_at' => 'datetime',
            'cy_closing_at' => 'datetime',
            'departure_date' => 'date',
            'eta_at' => 'datetime',
            'actual_arrival_at' => 'datetime',
            'loading_date' => 'date',
            'draft_pib_confirmed_at' => 'datetime',
            'billing_issued_at' => 'datetime',
            'billing_paid_at' => 'datetime',
            'billing_response_at' => 'datetime',
            'thc_paid_at' => 'datetime',
            'behandle_paid_at' => 'datetime',
            'do_released_at' => 'datetime',
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
     * @return BelongsToMany<HsCode, $this>
     */
    public function hsCodes(): BelongsToMany
    {
        return $this->belongsToMany(HsCode::class, 'bill_of_lading_hs_code');
    }

    /**
     * @return HasMany<Container, $this>
     */
    public function containers(): HasMany
    {
        return $this->hasMany(Container::class);
    }

    /**
     * @return HasMany<Attachment, $this>
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }

    /**
     * @return HasMany<ActivityLog, $this>
     */
    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
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

    public function isImport(): bool
    {
        return $this->shipment_type === ShipmentType::Import;
    }

    public function isExport(): bool
    {
        return $this->shipment_type === ShipmentType::Export;
    }

    /**
     * @return list<ShipmentMilestone>
     */
    public function milestoneSequence(): array
    {
        return $this->shipment_type
            ? ShipmentMilestone::sequence($this->shipment_type, $this->billing_response)
            : [];
    }

    public function milestonePosition(): int
    {
        $index = $this->current_milestone
            ? array_search($this->current_milestone, $this->milestoneSequence(), true)
            : false;

        return $index === false ? 1 : $index + 1;
    }

    public function nextMilestone(): ?ShipmentMilestone
    {
        return $this->shipment_type
            ? ShipmentMilestone::next($this->shipment_type, $this->billing_response, $this->current_milestone)
            : null;
    }

    public function previousMilestone(): ?ShipmentMilestone
    {
        return $this->shipment_type
            ? ShipmentMilestone::previous($this->shipment_type, $this->billing_response, $this->current_milestone)
            : null;
    }

    public function advanceMilestone(): void
    {
        $next = $this->nextMilestone();

        if (! $next) {
            return;
        }

        $this->current_milestone = $next;

        // Reaching the last milestone completes the shipment; stepping back
        // from it reopens it.
        if ($this->nextMilestone() === null) {
            $this->status = BillOfLadingStatus::Completed;
            $this->completed_at ??= now();
        } elseif ($this->status === BillOfLadingStatus::Completed) {
            $this->status = BillOfLadingStatus::InProgress;
            $this->completed_at = null;
        }

        $this->save();
    }

    public function regressMilestone(): void
    {
        $previous = $this->previousMilestone();

        if (! $previous) {
            return;
        }

        $this->current_milestone = $previous;

        if ($this->status === BillOfLadingStatus::Completed) {
            $this->status = BillOfLadingStatus::InProgress;
            $this->completed_at = null;
        }

        $this->save();
    }
}
