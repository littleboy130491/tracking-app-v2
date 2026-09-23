<?php

/**
 * File: app/Services/ActivityLogger.php
 * Responsibility: Writes append-only audit entries for shipment changes.
 * What it does:
 * - Records the event, actor, affected entity and before/after values, linked
 *   to the export/import shipment and (optionally) one of its containers.
 * - Can snapshot shipment data (shipment, containers, HS codes) and record
 *   only the fields that actually changed between two snapshots.
 * - Stores a customer-safe summary the portal can quote.
 * How to use: call `record()` inside the same transaction as the change it logs.
 * How to extend: add new event names; never update or delete existing rows.
 */

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\ExportContainer;
use App\Models\ExportShipment;
use App\Models\ImportContainer;
use App\Models\ImportShipment;
use App\Models\Note;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class ActivityLogger
{
    /** @var list<string> */
    private const IGNORED_ATTRIBUTES = [
        'id',
        'export_shipment_id',
        'import_shipment_id',
        'export_container_id',
        'import_container_id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * @param  array<string, mixed>|null  $oldValues
     * @param  array<string, mixed>|null  $newValues
     */
    public function record(
        ExportShipment|ImportShipment $shipment,
        string $event,
        string $entityType,
        int $entityId,
        ExportContainer|ImportContainer|null $container = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $customerSummary = null,
        ?User $actor = null,
        ?CarbonInterface $occurredAt = null,
    ): ActivityLog {
        $links = [$shipment->activityLogShipmentKey() => $shipment->getKey()];

        if ($container) {
            $links[$container->activityLogContainerKey()] = $container->getKey();
        }

        $log = ActivityLog::query()->create([
            ...$links,
            'actor_id' => $actor?->getKey() ?? auth()->id(),
            'event' => $event,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'customer_summary' => $customerSummary,
            'occurred_at' => $occurredAt ?? now(),
        ]);

        return $log;
    }

    /**
     * @return array{
     *     shipment: array<string, mixed>,
     *     containers: array<int, array<string, mixed>>,
     *     hs_codes: list<string>
     * }
     */
    public function shipmentSnapshot(ExportShipment|ImportShipment $shipment): array
    {
        $shipment->refresh();

        return [
            'shipment' => $this->auditableAttributes($shipment),
            'containers' => $shipment->containers()
                ->with('attachments')
                ->get()
                ->mapWithKeys(fn (Model $container): array => [
                    $container->getKey() => $this->auditableAttributes($container)
                        + ['attachments' => $container->attachments->pluck('id')->sort()->values()->all()],
                ])
                ->all(),
            'hs_codes' => $shipment instanceof ImportShipment
                ? $shipment->hsCodes()->orderBy('code')->pluck('code')->values()->all()
                : [],
        ];
    }

    /** @return array<string, mixed> */
    public function containerSnapshot(ExportContainer|ImportContainer $container): array
    {
        $container->refresh();

        return $this->auditableAttributes($container)
            + ['attachments' => $container->attachments()->pluck('id')->sort()->values()->all()];
    }

    /**
     * Records every shipment, nested-container and HS-code assignment change
     * made since `$before`. Unchanged saves produce no activity rows.
     *
     * @param array{
     *     shipment: array<string, mixed>,
     *     containers: array<int, array<string, mixed>>,
     *     hs_codes: list<string>
     * } $before
     */
    public function recordShipmentChanges(ExportShipment|ImportShipment $shipment, array $before): int
    {
        $after = $this->shipmentSnapshot($shipment);
        $recorded = 0;

        if ($this->recordAttributeChanges(
            $shipment,
            $shipment,
            $before['shipment'],
            $after['shipment'],
            'shipment_updated',
            'Updated shipment fields: ',
        )) {
            $recorded++;
        }

        $beforeContainers = $before['containers'];
        $afterContainers = $after['containers'];
        $containerIds = array_unique([...array_keys($beforeContainers), ...array_keys($afterContainers)]);

        foreach ($containerIds as $containerId) {
            $container = $shipment->containers()->withTrashed()->find($containerId);

            if (! $container) {
                continue;
            }

            $hadContainer = array_key_exists($containerId, $beforeContainers);
            $hasContainer = array_key_exists($containerId, $afterContainers);

            if (! $hadContainer && $hasContainer) {
                $values = $this->nonNullValues($afterContainers[$containerId]);

                $this->record(
                    $shipment,
                    'container_created',
                    $container::class,
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
                    $shipment,
                    'container_deleted',
                    $container::class,
                    $container->getKey(),
                    container: $container,
                    oldValues: $values,
                    customerSummary: 'Container '.$container->container_number.' removed.',
                );
                $recorded++;

                continue;
            }

            if ($this->recordAttributeChanges(
                $shipment,
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
                $shipment,
                'hs_codes_updated',
                $shipment::class,
                $shipment->getKey(),
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
    public function recordContainerChanges(ExportContainer|ImportContainer $container, array $before): ?ActivityLog
    {
        $after = $this->containerSnapshot($container);

        return $this->recordAttributeChanges(
            $container->shipment,
            $container,
            $before,
            $after,
            'container_updated',
            'Updated container '.$container->container_number.': ',
            $container,
        );
    }

    public function recordContainerCreated(ExportContainer|ImportContainer $container): ActivityLog
    {
        $container->refresh();

        return $this->record(
            $container->shipment,
            'container_created',
            $container::class,
            $container->getKey(),
            container: $container,
            newValues: $this->nonNullValues($this->containerSnapshot($container)),
            customerSummary: 'Container '.$container->container_number.' created.',
        );
    }

    /**
     * Records a note event against its target. Notes on a shipment or
     * container keep the shipment/container linkage the operator scope and
     * the portal read; notes on companies or users leave the links null.
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

        $links = match (true) {
            $noteable instanceof ExportShipment, $noteable instanceof ImportShipment => [
                $noteable->activityLogShipmentKey() => $noteable->getKey(),
            ],
            // Containers read their parent FK column by name so the same code
            // serves both processes.
            $noteable instanceof ExportContainer, $noteable instanceof ImportContainer => [
                $noteable->activityLogShipmentKey() => $noteable->{$noteable->activityLogShipmentKey()},
                $noteable->activityLogContainerKey() => $noteable->getKey(),
            ],
            default => [],
        };

        $log = ActivityLog::query()->create([
            ...$links,
            'actor_id' => $actor?->getKey() ?? auth()->id(),
            'event' => $event,
            'entity_type' => Note::class,
            'entity_id' => $note->getKey(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'customer_summary' => null,
            'occurred_at' => now(),
        ]);

        return $log;
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
        ExportShipment|ImportShipment $shipment,
        Model $entity,
        array $before,
        array $after,
        string $event,
        string $summaryPrefix,
        ExportContainer|ImportContainer|null $container = null,
    ): ?ActivityLog {
        [$oldValues, $newValues] = $this->changedValues($before, $after);

        if ($oldValues === []) {
            return null;
        }

        return $this->record(
            $shipment,
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
