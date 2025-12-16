<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\PartnerService;
use Illuminate\Auth\Access\HandlesAuthorization;

class PartnerServicePolicy
{
    use HandlesAuthorization;
    
    public function view(AuthUser $authUser, PartnerService $partnerService): bool
    {
        return $authUser->can('View:PartnerService');
    }

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:PartnerService');
    }

}