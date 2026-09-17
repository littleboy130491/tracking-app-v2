<?php

/**
 * File: app/Models/Company.php
 * Responsibility: A customer company that owns shipments.
 * What it does:
 * - Holds company identity/contact data and its shipments (bills of lading).
 * - Links to the users who handle it: `customers()` for portal access,
 *   `operators()` for assigned staff (same company_user pivot, split by role).
 * How to use: `$company->billOfLadings`, `$company->customers`, `$company->operators`.
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

    /**
     * Linked users carrying the customer role (portal access).
     *
     * @return BelongsToMany<User, $this>
     */
    public function customers(): BelongsToMany
    {
        return $this->users()->role(Role::CUSTOMER);
    }

    /**
     * Linked users carrying the operator role (assigned staff).
     *
     * @return BelongsToMany<User, $this>
     */
    public function operators(): BelongsToMany
    {
        return $this->users()->role(Role::OPERATOR);
    }

    /**
     * Sync one role's links without touching the other role's rows.
     * A plain sync() on the filtered relation would detach every pivot row
     * outside the given ids — including the other role — because the detach
     * query cannot see the role filter. So the diff is computed from the
     * role-scoped relation and detached explicitly.
     *
     * @param  'customers'|'operators'  $relation
     * @param  list<int>  $userIds
     */
    public function syncLinkedUsers(string $relation, array $userIds): void
    {
        $userIds = array_map('intval', $userIds);
        $currentlyLinked = $this->{$relation}()->pluck('users.id')->all();

        $this->users()->detach(array_diff($currentlyLinked, $userIds));
        $this->users()->syncWithoutDetaching($userIds);
    }
}
