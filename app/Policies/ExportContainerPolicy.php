<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ExportContainer;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class ExportContainerPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ExportContainer');
    }

    public function view(AuthUser $authUser, ExportContainer $exportContainer): bool
    {
        return $authUser->can('View:ExportContainer');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ExportContainer');
    }

    public function update(AuthUser $authUser, ExportContainer $exportContainer): bool
    {
        return $authUser->can('Update:ExportContainer');
    }

    public function delete(AuthUser $authUser, ExportContainer $exportContainer): bool
    {
        return $authUser->can('Delete:ExportContainer');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:ExportContainer');
    }

    public function restore(AuthUser $authUser, ExportContainer $exportContainer): bool
    {
        return $authUser->can('Restore:ExportContainer');
    }

    public function forceDelete(AuthUser $authUser, ExportContainer $exportContainer): bool
    {
        return $authUser->can('ForceDelete:ExportContainer');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ExportContainer');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ExportContainer');
    }

    public function replicate(AuthUser $authUser, ExportContainer $exportContainer): bool
    {
        return $authUser->can('Replicate:ExportContainer');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ExportContainer');
    }
}
