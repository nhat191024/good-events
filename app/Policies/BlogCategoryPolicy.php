<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\BlogCategory;
use Illuminate\Auth\Access\HandlesAuthorization;

class BlogCategoryPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:BlogCategory');
    }

    public function view(AuthUser $authUser, BlogCategory $blogCategory): bool
    {
        return $authUser->can('View:BlogCategory');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:BlogCategory');
    }

    public function update(AuthUser $authUser, BlogCategory $blogCategory): bool
    {
        return $authUser->can('Update:BlogCategory');
    }

    public function delete(AuthUser $authUser, BlogCategory $blogCategory): bool
    {
        return $authUser->can('Delete:BlogCategory');
    }

    public function restore(AuthUser $authUser, BlogCategory $blogCategory): bool
    {
        return $authUser->can('Restore:BlogCategory');
    }

}