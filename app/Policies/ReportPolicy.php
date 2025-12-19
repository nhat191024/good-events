<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Report;
use Illuminate\Auth\Access\HandlesAuthorization;

class ReportPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Report');
    }

    public function view(AuthUser $authUser, Report $report): bool
    {
        return $authUser->can('View:Report');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Report');
    }

    public function update(AuthUser $authUser, Report $report): bool
    {
        return $authUser->can('Update:Report');
    }

    public function delete(AuthUser $authUser, Report $report): bool
    {
        return $authUser->can('Delete:Report');
    }

    public function restore(AuthUser $authUser, Report $report): bool
    {
        return $authUser->can('Restore:Report');
    }

}