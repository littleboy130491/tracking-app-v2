<?php

/**
 * File: app/Models/HsCode.php
 * Responsibility: A harmonized-system code (master data).
 * What it does:
 * - Shared across shipments via two pivots (export_shipment_hs_code and
 *   import_shipment_hs_code), so one code + description is defined once and
 *   reused.
 * How to use: `$hsCode->exportShipments`, `$hsCode->importShipments`.
 * How to extend: Add columns such as duty rate; pivot extras live on the
 *   shipment-specific pivot tables.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['code', 'description'])]
class HsCode extends Model
{
    use HasFactory;

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
}
