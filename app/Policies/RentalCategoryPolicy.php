<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\RentalCategory;
use Illuminate\Auth\Access\HandlesAuthorization;

class RentalCategoryPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:RentalCategory');
    }

    public function view(AuthUser $authUser, RentalCategory $rentalCategory): bool
    {
        return $authUser->can('View:RentalCategory');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:RentalCategory');
    }

    public function update(AuthUser $authUser, RentalCategory $rentalCategory): bool
    {
        return $authUser->can('Update:RentalCategory');
    }

    public function delete(AuthUser $authUser, RentalCategory $rentalCategory): bool
    {
        return $authUser->can('Delete:RentalCategory');
    }

    public function restore(AuthUser $authUser, RentalCategory $rentalCategory): bool
    {
        return $authUser->can('Restore:RentalCategory');
    }

}