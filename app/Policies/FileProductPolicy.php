<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\FileProduct;
use Illuminate\Auth\Access\HandlesAuthorization;

class FileProductPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:FileProduct');
    }

    public function view(AuthUser $authUser, FileProduct $fileProduct): bool
    {
        return $authUser->can('View:FileProduct');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:FileProduct');
    }

    public function update(AuthUser $authUser, FileProduct $fileProduct): bool
    {
        return $authUser->can('Update:FileProduct');
    }

    public function delete(AuthUser $authUser, FileProduct $fileProduct): bool
    {
        return $authUser->can('Delete:FileProduct');
    }

    public function restore(AuthUser $authUser, FileProduct $fileProduct): bool
    {
        return $authUser->can('Restore:FileProduct');
    }

}