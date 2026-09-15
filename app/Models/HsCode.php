<?php

/**
 * File: app/Models/HsCode.php
 * Responsibility: A harmonized-system code (master data).
 * What it does:
 * - Shared across shipments via the bill_of_lading_hs_code pivot, so one
 *   code + description is defined once and reused.
 * How to use: `$hsCode->billOfLadings`; managed under Shipments → HS codes.
 * How to extend: Add columns such as duty rate; pivot extras live on
 *   bill_of_lading_hs_code.
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
     * @return BelongsToMany<BillOfLading, $this>
     */
    public function billOfLadings(): BelongsToMany
    {
        return $this->belongsToMany(BillOfLading::class, 'bill_of_lading_hs_code');
    }
}
