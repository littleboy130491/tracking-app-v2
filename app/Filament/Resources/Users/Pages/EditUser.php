<?php

/**
 * File: app/Filament/Resources/Users/Pages/EditUser.php
 * Responsibility: Edits a user.
 * What it does:
 * - Standard Filament edit screen. Roles a user may change are controlled by
 *   App\Support\Authorization\AssignableRoles in the form, which Filament also
 *   enforces when the save is submitted.
 * - Header offers Impersonate (admin/super_admin only, see User model).
 * How to use: Reached by editing a user.
 * How to extend: Add header actions; put authorisation in the services.
 */

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\EditRecord;
use STS\FilamentImpersonate\Actions\Impersonate;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Impersonate::make()->record($this->getRecord())->redirectTo('/'),
            $this->getSaveFormAction(),
        ];
    }
}
