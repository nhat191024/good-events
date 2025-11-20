<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\DesignCategory;
use Illuminate\Auth\Access\HandlesAuthorization;

class DesignCategoryPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:DesignCategory');
    }

    public function view(AuthUser $authUser, DesignCategory $designCategory): bool
    {
        return $authUser->can('View:DesignCategory');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:DesignCategory');
    }

    public function update(AuthUser $authUser, DesignCategory $designCategory): bool
    {
        return $authUser->can('Update:DesignCategory');
    }

    public function delete(AuthUser $authUser, DesignCategory $designCategory): bool
    {
        return $authUser->can('Delete:DesignCategory');
    }

    public function restore(AuthUser $authUser, DesignCategory $designCategory): bool
    {
        return $authUser->can('Restore:DesignCategory');
    }

}