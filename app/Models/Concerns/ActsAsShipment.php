<?php

/**
 * File: app/Models/Concerns/ActsAsShipment.php
 * Responsibility: Behaviour shared by the export and import shipment models.
 * What it does:
 * - Applies the create defaults (company name snapshot, document received
 *   date/by) so both processes behave the same.
 * - Implements the milestone helpers used by the admin stepper and pages.
 * - pickerLabel() is the never-null B/L caption used by Filament shipment pickers.
 * How to use: `use ActsAsShipment;` in ExportShipment / ImportShipment and
 *   declare `milestoneEnum()`.
 * How to extend: add shared shipment behaviour here; process-specific fields
 *   stay in the model and its form.
 */

namespace App\Models\Concerns;

use App\Enums\ExportMilestone;
use App\Enums\ImportMilestone;
use App\Enums\ShipmentStatus;
use App\Services\ActivityLogger;
use BackedEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait ActsAsShipment
{
    /**
     * The milestone enum backing this shipment model.
     *
     * @return class-string<ExportMilestone|ImportMilestone>
     */
    abstract public static function milestoneEnum(): string;

    protected static function bootActsAsShipment(): void
    {
        static::creating(function (Model $shipment): void {
            $shipment->company_name_snapshot ??= $shipment->company?->name;
            $shipment->document_received_date ??= today();
            $shipment->document_received_by ??= auth()->id();
        });
    }

    /**
     * The customer portal only lists and opens shipments that have left the
     * draft state; drafts stay internal until the process starts.
     */
    public function scopeVisibleInPortal(Builder $query): Builder
    {
        return $query->where('status', '!=', ShipmentStatus::Draft->value);
    }

    /**
     * Caption for Filament shipment selects/filters. `bl_number` is nullable
     * until Step 2; Select forbids a null option label.
     */
    public function pickerLabel(): string
    {
        return filled($this->bl_number)
            ? (string) $this->bl_number
            : 'No B/L yet (#'.$this->getKey().')';
    }

    /**
     * The milestones in order for this shipment (the import sequence drops the
     * SPJM branch unless the billing response was SPJM).
     *
     * @return list<BackedEnum>
     */
    public function milestoneSequence(): array
    {
        $enum = static::milestoneEnum();

        return $enum::sequence($this->billing_response ?? null);
    }

    public function milestonePosition(): int
    {
        $index = $this->current_milestone
            ? array_search($this->current_milestone, $this->milestoneSequence(), true)
            : false;

        return $index === false ? 1 : $index + 1;
    }

    public function nextMilestone(): ?BackedEnum
    {
        $enum = static::milestoneEnum();

        return $enum::next($this->billing_response ?? null, $this->current_milestone);
    }

    public function previousMilestone(): ?BackedEnum
    {
        $enum = static::milestoneEnum();

        return $enum::previous($this->billing_response ?? null, $this->current_milestone);
    }

    public function advanceMilestone(): void
    {
        if ($next = $this->nextMilestone()) {
            $this->moveToMilestone($next);
        }
    }

    public function regressMilestone(): void
    {
        if ($previous = $this->previousMilestone()) {
            $this->moveToMilestone($previous);
        }
    }

    /**
     * Sets the milestone directly — used by the stepper's click-to-jump and by
     * advance/regress. Reaching the last milestone completes the shipment;
     * moving off it reopens it; leaving the first milestone publishes a draft.
     * Every change is written to the activity log.
     */
    public function moveToMilestone(BackedEnum $target): void
    {
        $sequence = $this->milestoneSequence();

        if (! in_array($target, $sequence, true) || $target === $this->current_milestone) {
            return;
        }

        $from = $this->current_milestone;
        $this->current_milestone = $target;

        if ($target === end($sequence)) {
            $this->status = ShipmentStatus::Completed;
            $this->completed_at ??= now();
        } elseif ($this->status === ShipmentStatus::Completed) {
            $this->status = ShipmentStatus::InProgress;
            $this->completed_at = null;
        } elseif ($this->status === ShipmentStatus::Draft) {
            // Leaving "document received" means the process has started, which
            // publishes the shipment to the customer portal.
            $this->status = ShipmentStatus::InProgress;
        }

        $this->save();

        app(ActivityLogger::class)->record(
            shipment: $this,
            event: 'milestone_changed',
            entityType: $this::class,
            entityId: $this->getKey(),
            oldValues: ['milestone' => $from?->value],
            newValues: ['milestone' => $target->value],
            customerSummary: 'Progress moved to '.$target->getLabel(),
            customerVisible: true,
        );
    }
}
