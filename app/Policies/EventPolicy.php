<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Event;
use Illuminate\Auth\Access\HandlesAuthorization;

class EventPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Event');
    }

    public function view(AuthUser $authUser, Event $event): bool
    {
        return $authUser->can('View:Event');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Event');
    }

    public function update(AuthUser $authUser, Event $event): bool
    {
        return $authUser->can('Update:Event');
    }

    public function delete(AuthUser $authUser, Event $event): bool
    {
        return $authUser->can('Delete:Event');
    }

    public function restore(AuthUser $authUser, Event $event): bool
    {
        return $authUser->can('Restore:Event');
    }

}