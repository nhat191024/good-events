<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\FileProductBill;
use Illuminate\Auth\Access\HandlesAuthorization;

class FileProductBillPolicy
{
    use HandlesAuthorization;
    
    public function view(AuthUser $authUser, FileProductBill $fileProductBill): bool
    {
        return $authUser->can('View:FileProductBill');
    }

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:FileProductBill');
    }

}