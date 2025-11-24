<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\PartnerBill;
use Illuminate\Auth\Access\HandlesAuthorization;

class PartnerBillPolicy
{
    use HandlesAuthorization;
    
    public function view(AuthUser $authUser, PartnerBill $partnerBill): bool
    {
        return $authUser->can('View:PartnerBill');
    }

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:PartnerBill');
    }

}