<?php

/**
 * File: app/Models/ActivityLog.php
 * Responsibility: One append-only audit entry.
 * What it does:
 * - Records the event, actor, changed values and a customer-safe summary.
 * - Links to an export/import shipment and (optionally) one of its containers.
 * How to use: Written by App\Services\ActivityLogger in the same transaction as
 *   the change; never updated or deleted afterwards.
 * How to extend: Add new `event` values as new actions get logged.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'export_shipment_id', 'import_shipment_id', 'export_container_id', 'import_container_id',
    'actor_id', 'event', 'entity_type', 'entity_id', 'old_values', 'new_values',
    'customer_summary', 'occurred_at',
])]
class ActivityLog extends Model
{
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
            'occurred_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<ExportShipment, $this>
     */
    public function exportShipment(): BelongsTo
    {
        return $this->belongsTo(ExportShipment::class, 'export_shipment_id');
    }

    /**
     * @return BelongsTo<ImportShipment, $this>
     */
    public function importShipment(): BelongsTo
    {
        return $this->belongsTo(ImportShipment::class, 'import_shipment_id');
    }

    /**
     * @return BelongsTo<ExportContainer, $this>
     */
    public function exportContainer(): BelongsTo
    {
        return $this->belongsTo(ExportContainer::class, 'export_container_id');
    }

    /**
     * @return BelongsTo<ImportContainer, $this>
     */
    public function importContainer(): BelongsTo
    {
        return $this->belongsTo(ImportContainer::class, 'import_container_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    /**
     * The linked shipment, whichever process it belongs to.
     */
    public function linkedShipment(): ExportShipment|ImportShipment|null
    {
        return $this->export_shipment_id ? $this->exportShipment : $this->importShipment;
    }

    /**
     * The linked container, if the entry is about one.
     */
    public function linkedContainer(): ExportContainer|ImportContainer|null
    {
        return $this->export_container_id ? $this->exportContainer : $this->importContainer;
    }
}
