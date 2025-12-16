<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use Tapp\FilamentMailLog\Models\MailLog;
use Illuminate\Auth\Access\HandlesAuthorization;

class MailLogPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:MailLog');
    }

    public function view(AuthUser $authUser, MailLog $mailLog): bool
    {
        return $authUser->can('View:MailLog');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:MailLog');
    }

    public function update(AuthUser $authUser, MailLog $mailLog): bool
    {
        return $authUser->can('Update:MailLog');
    }

    public function delete(AuthUser $authUser, MailLog $mailLog): bool
    {
        return $authUser->can('Delete:MailLog');
    }

    public function restore(AuthUser $authUser, MailLog $mailLog): bool
    {
        return $authUser->can('Restore:MailLog');
    }

}