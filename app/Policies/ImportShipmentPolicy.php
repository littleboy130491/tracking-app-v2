<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ImportShipment;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class ImportShipmentPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ImportShipment');
    }

    public function view(AuthUser $authUser, ImportShipment $importShipment): bool
    {
        return $authUser->can('View:ImportShipment');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ImportShipment');
    }

    public function update(AuthUser $authUser, ImportShipment $importShipment): bool
    {
        return $authUser->can('Update:ImportShipment');
    }

    public function delete(AuthUser $authUser, ImportShipment $importShipment): bool
    {
        return $authUser->can('Delete:ImportShipment');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:ImportShipment');
    }

    public function restore(AuthUser $authUser, ImportShipment $importShipment): bool
    {
        return $authUser->can('Restore:ImportShipment');
    }

    public function forceDelete(AuthUser $authUser, ImportShipment $importShipment): bool
    {
        return $authUser->can('ForceDelete:ImportShipment');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ImportShipment');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ImportShipment');
    }

    public function replicate(AuthUser $authUser, ImportShipment $importShipment): bool
    {
        return $authUser->can('Replicate:ImportShipment');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ImportShipment');
    }
}
