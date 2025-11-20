<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\RentProduct;
use Illuminate\Auth\Access\HandlesAuthorization;

class RentProductPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:RentProduct');
    }

    public function view(AuthUser $authUser, RentProduct $rentProduct): bool
    {
        return $authUser->can('View:RentProduct');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:RentProduct');
    }

    public function update(AuthUser $authUser, RentProduct $rentProduct): bool
    {
        return $authUser->can('Update:RentProduct');
    }

    public function delete(AuthUser $authUser, RentProduct $rentProduct): bool
    {
        return $authUser->can('Delete:RentProduct');
    }

    public function restore(AuthUser $authUser, RentProduct $rentProduct): bool
    {
        return $authUser->can('Restore:RentProduct');
    }

}