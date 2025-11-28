<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use BinaryBuilds\FilamentFailedJobs\Models\FailedJob;
use Illuminate\Auth\Access\HandlesAuthorization;

class FailedJobPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:FailedJob');
    }

    public function view(AuthUser $authUser, FailedJob $failedJob): bool
    {
        return $authUser->can('View:FailedJob');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:FailedJob');
    }

    public function update(AuthUser $authUser, FailedJob $failedJob): bool
    {
        return $authUser->can('Update:FailedJob');
    }

    public function delete(AuthUser $authUser, FailedJob $failedJob): bool
    {
        return $authUser->can('Delete:FailedJob');
    }

    public function restore(AuthUser $authUser, FailedJob $failedJob): bool
    {
        return $authUser->can('Restore:FailedJob');
    }

}