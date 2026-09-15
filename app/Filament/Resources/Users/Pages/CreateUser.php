<?php

/**
 * File: app/Filament/Resources/Users/Pages/CreateUser.php
 * Responsibility: Creates a user, defaulting them to the customer role.
 * What it does:
 * - Guarantees the default: a user saved without a role becomes a customer, so
 *   the portal cannot end up with a role-less account.
 * - Which roles may be chosen (and submitted) is controlled by
 *   App\Support\Authorization\AssignableRoles in the form.
 * How to use: Reached from the Users resource "Create" button.
 * How to extend: Add post-create side effects in afterCreate().
 */

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\Role;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->getCreateFormAction(),
        ];
    }

    protected function afterCreate(): void
    {
        if ($this->record->roles()->count() === 0) {
            $this->record->syncRoles([Role::CUSTOMER]);
        }
    }
}
