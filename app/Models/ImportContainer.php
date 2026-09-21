<?php

/**
 * File: app/Models/ImportContainer.php
 * Responsibility: A container belonging to one import shipment.
 * What it does:
 * - Tracks the IMPORT.md container data: identity, cargo (description of
 *   goods, packages), gate-out, driver tracking (free-text position plus a
 *   validated URL), weights, factory loading and depot return.
 * - Carries its own HS codes through the import_container_hs_code pivot,
 *   defaulted from the parent shipment when the container is created.
 * - Owns its attachments; the photo pickers write through syncAttachments().
 * How to use: `$container->shipment`, `$container->hsCodes`, `$container->attachments`.
 * How to extend: Add import container fields as columns and expose them in the
 *   import container form or the shipment form's containers repeater.
 */

namespace App\Models;

use App\Enums\ContainerStatus;
use App\Enums\FactoryLoadingStatus;
use App\Models\Concerns\ActsAsContainer;
use App\Models\Concerns\HasNotes;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'import_shipment_id', 'container_number', 'size',
    'description_of_goods', 'packages',
    'driver_name', 'license_number', 'gate_out_cy_at',
    'tracking_position', 'tracking_position_url',
    'gross_weight', 'gross_weight_unit', 'cbm',
    'factory_loading_at', 'factory_loading_status',
    'return_depot_name', 'empty_returned_at',
    'status', 'latest_event', 'latest_event_at', 'completed_at', 'created_by', 'updated_by',
])]
class ImportContainer extends Model
{
    use ActsAsContainer, HasFactory, HasNotes, SoftDeletes;

    /** Column on `import_containers` pointing at its shipment. */
    protected const SHIPMENT_FK = 'import_shipment_id';

    /** Column on linked rows (attachments, activity logs) pointing at this container. */
    protected const CONTAINER_FK = 'import_container_id';

    /**
     * The activity-log column that links entries to this container's shipment.
     */
    public function activityLogShipmentKey(): string
    {
        return self::SHIPMENT_FK;
    }

    /**
     * The activity-log column that links entries to this container.
     */
    public function activityLogContainerKey(): string
    {
        return self::CONTAINER_FK;
    }

    /**
     * Seed a new container from its parent shipment: the cargo fields are
     * copied when left empty, and the shipment's HS codes are attached when
     * the container starts without any. Both hooks guard against a container
     * created without a shipment, so drafts never error.
     */
    protected static function booted(): void
    {
        static::creating(function (ImportContainer $container): void {
            if ($container->shipment === null) {
                return;
            }

            if (blank($container->description_of_goods)) {
                $container->description_of_goods = $container->shipment->goods_description;
            }

            if (blank($container->packages)) {
                $container->packages = $container->shipment->packages;
            }
        });

        static::created(function (ImportContainer $container): void {
            if ($container->shipment === null || $container->hsCodes()->exists()) {
                return;
            }

            $container->hsCodes()->syncWithoutDetaching(
                $container->shipment->hsCodes()->pluck('hs_codes.id')
            );
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'factory_loading_status' => FactoryLoadingStatus::class,
            'status' => ContainerStatus::class,
            'gate_out_cy_at' => 'datetime',
            'gross_weight' => 'decimal:3',
            'cbm' => 'decimal:3',
            'factory_loading_at' => 'datetime',
            'empty_returned_at' => 'datetime',
            'latest_event_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<ImportShipment, $this>
     */
    public function shipment(): BelongsTo
    {
        return $this->belongsTo(ImportShipment::class, 'import_shipment_id');
    }

    /**
     * @return BelongsToMany<HsCode, $this>
     */
    public function hsCodes(): BelongsToMany
    {
        return $this->belongsToMany(HsCode::class, 'import_container_hs_code');
    }

    /**
     * @return HasMany<Attachment, $this>
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class, 'import_container_id');
    }

    /**
     * @return HasMany<ActivityLog, $this>
     */
    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class, 'import_container_id')->latest('occurred_at');
    }
}
