<?php

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use Illuminate\Auth\Access\HandlesAuthorization;

class PartnerPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Partner');
    }

    public function view(AuthUser $authUser): bool
    {
        return $authUser->can('View:Partner');
    }

    public function update(AuthUser $authUser): bool
    {
        return $authUser->can('Update:Partner');
    }

    public function delete(AuthUser $authUser): bool
    {
        return $authUser->can('Delete:Partner');
    }

}