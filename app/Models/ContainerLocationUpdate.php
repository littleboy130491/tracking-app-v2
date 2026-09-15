<?php

/**
 * File: app/Models/ContainerLocationUpdate.php
 * Responsibility: One manual position report for a container.
 * What it does:
 * - History of locations instead of overwriting a single "last location" column.
 * - `is_customer_visible` gates whether the portal may show the entry.
 * How to use: `$container->locationUpdates`; append-only.
 * How to extend: Add accuracy/source columns when GPS feeds are integrated.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'container_id', 'location_name', 'latitude', 'longitude',
    'reported_at', 'notes', 'is_customer_visible', 'created_by',
])]
class ContainerLocationUpdate extends Model
{
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'reported_at' => 'datetime',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'is_customer_visible' => 'boolean',
        ];
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
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
