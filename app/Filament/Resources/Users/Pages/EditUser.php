<?php

/**
 * File: app/Filament/Resources/Users/Pages/EditUser.php
 * Responsibility: Eds a user.
 * What it does:
 * - Standard Filament edit screen. Roles a user may change are controlled by
 *   App\Support\Authorization\AssignableRoles in the form, which Filament also
 *   enforces when the save is submitted.
 * How to use: Reached by editing a user.
 * How to extend: Add header actions; put authorisation in the services.
 */

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->getSaveFormAction(),
        ];
    }
}
