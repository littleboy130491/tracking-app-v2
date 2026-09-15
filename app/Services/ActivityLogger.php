<?php

/**
 * File: app/Services/ActivityLogger.php
 * Responsibility: Writes append-only audit entries for shipment changes.
 * What it does:
 * - Records the event, actor, affected entity and before/after values.
 * - Stores a customer-safe summary plus the visibility flag the portal reads.
 * How to use: call `record()` inside the same transaction as the change it logs.
 * How to extend: add new event names; never update or delete existing rows.
 */

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\BillOfLading;
use App\Models\Container;
use App\Models\User;

class ActivityLogger
{
    /**
     * @param  array<string, mixed>|null  $oldValues
     * @param  array<string, mixed>|null  $newValues
     */
    public function record(
        BillOfLading $billOfLading,
        string $event,
        string $entityType,
        int $entityId,
        ?Container $container = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $customerSummary = null,
        bool $customerVisible = false,
        ?User $actor = null,
    ): ActivityLog {
        return ActivityLog::query()->create([
            'bill_of_lading_id' => $billOfLading->getKey(),
            'container_id' => $container?->getKey(),
            'actor_id' => $actor?->getKey() ?? auth()->id(),
            'event' => $event,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'customer_summary' => $customerSummary,
            'is_customer_visible' => $customerVisible,
            'occurred_at' => now(),
        ]);
    }
}
