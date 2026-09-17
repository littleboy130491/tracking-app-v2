<?php

/**
 * File: app/Services/ActivityLogger.php
 * Responsibility: Writes append-only audit entries for shipment changes.
 * What it does:
 * - Records the event, actor, affected entity and before/after values.
 * - Can snapshot shipment data (B/L, containers, HS codes) and record only
 *   the fields that actually changed between two snapshots.
 * - Stores a customer-safe summary plus the visibility flag the portal reads.
 * How to use: call `record()` inside the same transaction as the change it logs.
 * How to extend: add new event names; never update or delete existing rows.
 */

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\BillOfLading;
use App\Models\Container;
use App\Models\Note;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class ActivityLogger
{
    /** @var list<string> */
    private const IGNORED_ATTRIBUTES = [
        'id',
        'bill_of_lading_id',
        'created_at',
        'updated_at',
        'deleted_at',
        'created_by',
        'updated_by',
        'latest_event',
        'latest_event_at',
    ];

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
        $log = ActivityLog::query()->create([
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

        $this->stampLatestEvent($billOfLading, $container, $event);

        return $log;
    }

    /**
     * @return array{
     *     bill_of_lading: array<string, mixed>,
     *     containers: array<int, array<string, mixed>>,
     *     hs_codes: list<string>
     * }
     */
    public function shipmentSnapshot(BillOfLading $billOfLading): array
    {
        $billOfLading->refresh();

        return [
            'bill_of_lading' => $this->auditableAttributes($billOfLading),
            'containers' => $billOfLading->containers()
                ->with('attachments:id,container_id')
                ->get()
                ->mapWithKeys(fn (Container $container): array => [
                    $container->getKey() => $this->auditableAttributes($container)
                        + ['attachments' => $container->attachments->pluck('id')->sort()->values()->all()],
                ])
                ->all(),
            'hs_codes' => $billOfLading->hsCodes()
                ->orderBy('code')
                ->pluck('code')
                ->values()
                ->all(),
        ];
    }

    /** @return array<string, mixed> */
    public function containerSnapshot(Container $container): array
    {
        $container->refresh();

        return $this->auditableAttributes($container)
            + ['attachments' => $container->attachments()->pluck('id')->sort()->values()->all()];
    }

    /**
     * Records every B/L, nested-container and HS-code assignment change made
     * since `$before`. Unchanged saves produce no activity rows.
     *
     * @param array{
     *     bill_of_lading: array<string, mixed>,
     *     containers: array<int, array<string, mixed>>,
     *     hs_codes: list<string>
     * } $before
     */
    public function recordShipmentChanges(BillOfLading $billOfLading, array $before): int
    {
        $after = $this->shipmentSnapshot($billOfLading);
        $recorded = 0;

        if ($this->recordAttributeChanges(
            $billOfLading,
            $billOfLading,
            $before['bill_of_lading'],
            $after['bill_of_lading'],
            'bill_of_lading_updated',
            'Updated shipment fields: ',
        )) {
            $recorded++;
        }

        $beforeContainers = $before['containers'];
        $afterContainers = $after['containers'];
        $containerIds = array_unique([...array_keys($beforeContainers), ...array_keys($afterContainers)]);

        foreach ($containerIds as $containerId) {
            $container = Container::withTrashed()->find($containerId);

            if (! $container) {
                continue;
            }

            $hadContainer = array_key_exists($containerId, $beforeContainers);
            $hasContainer = array_key_exists($containerId, $afterContainers);

            if (! $hadContainer && $hasContainer) {
                $values = $this->nonNullValues($afterContainers[$containerId]);

                $this->record(
                    $billOfLading,
                    'container_created',
                    Container::class,
                    $container->getKey(),
                    container: $container,
                    newValues: $values,
                    customerSummary: 'Container '.$container->container_number.' created.',
                );
                $recorded++;

                continue;
            }

            if ($hadContainer && ! $hasContainer) {
                $values = $this->nonNullValues($beforeContainers[$containerId]);

                $this->record(
                    $billOfLading,
                    'container_deleted',
                    Container::class,
                    $container->getKey(),
                    container: $container,
                    oldValues: $values,
                    customerSummary: 'Container '.$container->container_number.' removed.',
                );
                $recorded++;

                continue;
            }

            if ($this->recordAttributeChanges(
                $billOfLading,
                $container,
                $beforeContainers[$containerId],
                $afterContainers[$containerId],
                'container_updated',
                'Updated container '.$container->container_number.': ',
                $container,
            )) {
                $recorded++;
            }
        }

        if ($before['hs_codes'] !== $after['hs_codes']) {
            $this->record(
                $billOfLading,
                'hs_codes_updated',
                BillOfLading::class,
                $billOfLading->getKey(),
                oldValues: ['hs_codes' => $before['hs_codes']],
                newValues: ['hs_codes' => $after['hs_codes']],
                customerSummary: 'Updated shipment HS codes.',
            );
            $recorded++;
        }

        return $recorded;
    }

    /**
     * @param  array<string, mixed>  $before
     */
    public function recordContainerChanges(Container $container, array $before): ?ActivityLog
    {
        $after = $this->containerSnapshot($container);

        return $this->recordAttributeChanges(
            $container->billOfLading,
            $container,
            $before,
            $after,
            'container_updated',
            'Updated container '.$container->container_number.': ',
            $container,
        );
    }

    public function recordContainerCreated(Container $container): ActivityLog
    {
        $container->refresh();

        return $this->record(
            $container->billOfLading,
            'container_created',
            Container::class,
            $container->getKey(),
            container: $container,
            newValues: $this->nonNullValues($this->containerSnapshot($container)),
            customerSummary: 'Container '.$container->container_number.' created.',
        );
    }

    /**
     * Records a note event against its target. Notes on a shipment or
     * container keep the B/L/container linkage the operator scope and the
     * portal read; notes on companies or users leave both columns null.
     *
     * @param  array<string, mixed>|null  $oldValues
     * @param  array<string, mixed>|null  $newValues
     */
    public function recordNote(
        Note $note,
        string $event,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?User $actor = null,
    ): ActivityLog {
        $noteable = $note->noteable;

        $log = ActivityLog::query()->create([
            'bill_of_lading_id' => match (true) {
                $noteable instanceof BillOfLading => $noteable->getKey(),
                $noteable instanceof Container => $noteable->bill_of_lading_id,
                default => null,
            },
            'container_id' => $noteable instanceof Container ? $noteable->getKey() : null,
            'actor_id' => $actor?->getKey() ?? auth()->id(),
            'event' => $event,
            'entity_type' => Note::class,
            'entity_id' => $note->getKey(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'customer_summary' => null,
            'is_customer_visible' => false,
            'occurred_at' => now(),
        ]);

        if ($noteable instanceof BillOfLading) {
            $this->stampLatestEvent($noteable, null, $event);
        } elseif ($noteable instanceof Container) {
            $this->stampLatestEvent($noteable->billOfLading, $noteable, $event);
        }

        return $log;
    }

    /**
     * Keeps the denormalized latest-event columns current. Every recorded
     * event stamps the shipment; container-level events also stamp the
     * container itself, so lists can show "what happened last" without
     * touching the log table.
     */
    private function stampLatestEvent(BillOfLading $billOfLading, ?Container $container, string $event): void
    {
        $stamp = ['latest_event' => $event, 'latest_event_at' => now()];

        $billOfLading->forceFill($stamp)->save();
        $container?->forceFill($stamp)->save();
    }

    /** @return array<string, mixed> */
    private function auditableAttributes(Model $model): array
    {
        return Arr::except($model->getAttributes(), self::IGNORED_ATTRIBUTES);
    }

    /**
     * @param  array<string, mixed>  $before
     * @param  array<string, mixed>  $after
     */
    private function recordAttributeChanges(
        BillOfLading $billOfLading,
        Model $entity,
        array $before,
        array $after,
        string $event,
        string $summaryPrefix,
        ?Container $container = null,
    ): ?ActivityLog {
        [$oldValues, $newValues] = $this->changedValues($before, $after);

        if ($oldValues === []) {
            return null;
        }

        return $this->record(
            $billOfLading,
            $event,
            $entity::class,
            $entity->getKey(),
            container: $container,
            oldValues: $oldValues,
            newValues: $newValues,
            customerSummary: $summaryPrefix.$this->fieldList(array_keys($newValues)).'.',
        );
    }

    /**
     * @param  array<string, mixed>  $before
     * @param  array<string, mixed>  $after
     * @return array{array<string, mixed>, array<string, mixed>}
     */
    private function changedValues(array $before, array $after): array
    {
        $oldValues = [];
        $newValues = [];

        foreach (array_unique([...array_keys($before), ...array_keys($after)]) as $field) {
            $old = $before[$field] ?? null;
            $new = $after[$field] ?? null;

            if ($old === $new) {
                continue;
            }

            $oldValues[$field] = $old;
            $newValues[$field] = $new;
        }

        return [$oldValues, $newValues];
    }

    /** @param array<string, mixed> $values */
    private function nonNullValues(array $values): array
    {
        return array_filter($values, fn (mixed $value): bool => $value !== null);
    }

    /** @param list<string> $fields */
    private function fieldList(array $fields): string
    {
        return implode(', ', array_map(fn (string $field): string => Str::headline($field), $fields));
    }
}
