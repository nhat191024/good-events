<?php

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use Illuminate\Auth\Access\HandlesAuthorization;

class CustomerPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Customer');
    }

    public function view(AuthUser $authUser): bool
    {
        return $authUser->can('View:Customer');
    }

    public function update(AuthUser $authUser): bool
    {
        return $authUser->can('Update:Customer');
    }

    public function delete(AuthUser $authUser): bool
    {
        return $authUser->can('Delete:Customer');
    }

}