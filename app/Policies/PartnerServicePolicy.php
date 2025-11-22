<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\PartnerService;
use Illuminate\Auth\Access\HandlesAuthorization;

use Filament\Facades\Filament;

class PartnerServicePolicy
{
    use HandlesAuthorization;

    public function view(AuthUser $authUser, PartnerService $partnerService): bool
    {
        return $authUser->can('View:PartnerService');
    }

    public function viewAny(AuthUser $authUser): bool
    {
        if (Filament::getCurrentPanel()->getId() === 'partner') {
            return true;
        }
        return $authUser->can('ViewAny:PartnerService');
    }
}
