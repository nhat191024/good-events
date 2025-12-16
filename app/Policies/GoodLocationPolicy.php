<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\GoodLocation;
use Illuminate\Auth\Access\HandlesAuthorization;

class GoodLocationPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:GoodLocation');
    }

    public function view(AuthUser $authUser, GoodLocation $goodLocation): bool
    {
        return $authUser->can('View:GoodLocation');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:GoodLocation');
    }

    public function update(AuthUser $authUser, GoodLocation $goodLocation): bool
    {
        return $authUser->can('Update:GoodLocation');
    }

    public function delete(AuthUser $authUser, GoodLocation $goodLocation): bool
    {
        return $authUser->can('Delete:GoodLocation');
    }

    public function restore(AuthUser $authUser, GoodLocation $goodLocation): bool
    {
        return $authUser->can('Restore:GoodLocation');
    }

}