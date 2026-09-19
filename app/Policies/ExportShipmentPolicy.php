<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ExportShipment;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class ExportShipmentPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ExportShipment');
    }

    public function view(AuthUser $authUser, ExportShipment $exportShipment): bool
    {
        return $authUser->can('View:ExportShipment');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ExportShipment');
    }

    public function update(AuthUser $authUser, ExportShipment $exportShipment): bool
    {
        return $authUser->can('Update:ExportShipment');
    }

    public function delete(AuthUser $authUser, ExportShipment $exportShipment): bool
    {
        return $authUser->can('Delete:ExportShipment');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:ExportShipment');
    }

    public function restore(AuthUser $authUser, ExportShipment $exportShipment): bool
    {
        return $authUser->can('Restore:ExportShipment');
    }

    public function forceDelete(AuthUser $authUser, ExportShipment $exportShipment): bool
    {
        return $authUser->can('ForceDelete:ExportShipment');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ExportShipment');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ExportShipment');
    }

    public function replicate(AuthUser $authUser, ExportShipment $exportShipment): bool
    {
        return $authUser->can('Replicate:ExportShipment');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ExportShipment');
    }
}
