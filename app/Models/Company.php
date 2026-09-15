<?php

/**
 * File: app/Models/Company.php
 * Responsibility: A customer company that owns shipments.
 * What it does:
 * - Holds company identity/contact data and its shipments (bills of lading).
 * - Links to the portal users who may handle it.
 * How to use: `$company->billOfLadings`, `$company->users`.
 * How to extend: Add company-level fields as columns on the companies table.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'code', 'email', 'phone', 'address', 'is_active'])]
class Company extends Model
{
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return HasMany<BillOfLading, $this>
     */
    public function billOfLadings(): HasMany
    {
        return $this->hasMany(BillOfLading::class);
    }

    /**
     * Users who may view this company's shipments in the portal.
     *
     * @return BelongsToMany<User, $this>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }
}
