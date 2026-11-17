<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enum\Role;
use App\Models\RevenueRecord;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class RevenueRecordPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->hasRole(Role::SUPER_ADMIN->value) || $authUser->can('ViewAny:RevenueRecord');
    }

    public function view(AuthUser $authUser, RevenueRecord $revenueRecord): bool
    {
        return $authUser->hasRole(Role::SUPER_ADMIN->value) || $authUser->can('View:RevenueRecord');
    }
}
