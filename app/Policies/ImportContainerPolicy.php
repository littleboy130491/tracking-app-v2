<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ImportContainer;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class ImportContainerPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ImportContainer');
    }

    public function view(AuthUser $authUser, ImportContainer $importContainer): bool
    {
        return $authUser->can('View:ImportContainer');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ImportContainer');
    }

    public function update(AuthUser $authUser, ImportContainer $importContainer): bool
    {
        return $authUser->can('Update:ImportContainer');
    }

    public function delete(AuthUser $authUser, ImportContainer $importContainer): bool
    {
        return $authUser->can('Delete:ImportContainer');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:ImportContainer');
    }

    public function restore(AuthUser $authUser, ImportContainer $importContainer): bool
    {
        return $authUser->can('Restore:ImportContainer');
    }

    public function forceDelete(AuthUser $authUser, ImportContainer $importContainer): bool
    {
        return $authUser->can('ForceDelete:ImportContainer');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ImportContainer');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ImportContainer');
    }

    public function replicate(AuthUser $authUser, ImportContainer $importContainer): bool
    {
        return $authUser->can('Replicate:ImportContainer');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ImportContainer');
    }
}
