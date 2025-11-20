<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\EventOrganizationGuide;
use Illuminate\Auth\Access\HandlesAuthorization;

class EventOrganizationGuidePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:EventOrganizationGuide');
    }

    public function view(AuthUser $authUser, EventOrganizationGuide $eventOrganizationGuide): bool
    {
        return $authUser->can('View:EventOrganizationGuide');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:EventOrganizationGuide');
    }

    public function update(AuthUser $authUser, EventOrganizationGuide $eventOrganizationGuide): bool
    {
        return $authUser->can('Update:EventOrganizationGuide');
    }

    public function delete(AuthUser $authUser, EventOrganizationGuide $eventOrganizationGuide): bool
    {
        return $authUser->can('Delete:EventOrganizationGuide');
    }

    public function restore(AuthUser $authUser, EventOrganizationGuide $eventOrganizationGuide): bool
    {
        return $authUser->can('Restore:EventOrganizationGuide');
    }

}