<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use Spatie\Tags\Tag;
use Illuminate\Auth\Access\HandlesAuthorization;

class TagPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Tag');
    }

    public function view(AuthUser $authUser, Tag $tag): bool
    {
        return $authUser->can('View:Tag');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Tag');
    }

    public function update(AuthUser $authUser, Tag $tag): bool
    {
        return $authUser->can('Update:Tag');
    }

    public function delete(AuthUser $authUser, Tag $tag): bool
    {
        return $authUser->can('Delete:Tag');
    }

    public function restore(AuthUser $authUser, Tag $tag): bool
    {
        return $authUser->can('Restore:Tag');
    }

}