<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\PartnerBill;
use Illuminate\Auth\Access\HandlesAuthorization;

use Filament\Facades\Filament;

class PartnerBillPolicy
{
    use HandlesAuthorization;

    public function view(AuthUser $authUser, PartnerBill $partnerBill): bool
    {
        return $authUser->can('View:PartnerBill');
    }

    public function viewAny(AuthUser $authUser): bool
    {
        if (Filament::getCurrentPanel()->getId() === 'partner') {
            return true;
        }
        return $authUser->can('ViewAny:PartnerBill');
    }
}
