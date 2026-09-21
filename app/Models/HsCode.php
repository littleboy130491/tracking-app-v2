<?php

/**
 * File: app/Models/HsCode.php
 * Responsibility: A harmonized-system code (master data).
 * What it does:
 * - Shared across shipments and containers via three pivots
 *   (export_shipment_hs_code, import_shipment_hs_code and
 *   import_container_hs_code), so one code + description is defined once and
 *   reused.
 * How to use: `$hsCode->exportShipments`, `$hsCode->importShipments`,
 *   `$hsCode->importContainers`.
 * How to extend: Add columns such as duty rate; pivot extras live on the
 *   shipment- or container-specific pivot tables.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['code', 'description'])]
class HsCode extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * @return BelongsToMany<ExportShipment, $this>
     */
    public function exportShipments(): BelongsToMany
    {
        return $this->belongsToMany(ExportShipment::class, 'export_shipment_hs_code');
    }

    /**
     * @return BelongsToMany<ImportShipment, $this>
     */
    public function importShipments(): BelongsToMany
    {
        return $this->belongsToMany(ImportShipment::class, 'import_shipment_hs_code');
    }

    /**
     * @return BelongsToMany<ImportContainer, $this>
     */
    public function importContainers(): BelongsToMany
    {
        return $this->belongsToMany(ImportContainer::class, 'import_container_hs_code');
    }
}
