<?php

/**
 * File: app/Models/ActivityLog.php
 * Responsibility: One append-only audit entry.
 * What it does:
 * - Records the event, actor, changed values and a customer-safe summary.
 * - `is_customer_visible` limits what the portal may read.
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
    'bill_of_lading_id', 'container_id', 'actor_id', 'event',
    'entity_type', 'entity_id', 'old_values', 'new_values', 'customer_summary',
    'is_customer_visible', 'occurred_at',
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
            'is_customer_visible' => 'boolean',
            'occurred_at' => 'datetime',
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
     * @return BelongsTo<Container, $this>
     */
    public function container(): BelongsTo
    {
        return $this->belongsTo(Container::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
