<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\HsCode;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class HsCodePolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:HsCode');
    }

    public function view(AuthUser $authUser, HsCode $hsCode): bool
    {
        return $authUser->can('View:HsCode');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:HsCode');
    }

    public function update(AuthUser $authUser, HsCode $hsCode): bool
    {
        return $authUser->can('Update:HsCode');
    }

    public function delete(AuthUser $authUser, HsCode $hsCode): bool
    {
        return $authUser->can('Delete:HsCode');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:HsCode');
    }
}
