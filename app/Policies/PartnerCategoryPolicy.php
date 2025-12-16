<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\PartnerCategory;
use Illuminate\Auth\Access\HandlesAuthorization;

class PartnerCategoryPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:PartnerCategory');
    }

    public function view(AuthUser $authUser, PartnerCategory $partnerCategory): bool
    {
        return $authUser->can('View:PartnerCategory');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:PartnerCategory');
    }

    public function update(AuthUser $authUser, PartnerCategory $partnerCategory): bool
    {
        return $authUser->can('Update:PartnerCategory');
    }

    public function delete(AuthUser $authUser, PartnerCategory $partnerCategory): bool
    {
        return $authUser->can('Delete:PartnerCategory');
    }

    public function restore(AuthUser $authUser, PartnerCategory $partnerCategory): bool
    {
        return $authUser->can('Restore:PartnerCategory');
    }

}