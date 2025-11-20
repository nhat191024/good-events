<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use BeyondCode\Vouchers\Models\Voucher;
use Illuminate\Auth\Access\HandlesAuthorization;

class VoucherPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Voucher');
    }

    public function view(AuthUser $authUser, Voucher $voucher): bool
    {
        return $authUser->can('View:Voucher');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Voucher');
    }

    public function update(AuthUser $authUser, Voucher $voucher): bool
    {
        return $authUser->can('Update:Voucher');
    }

    public function delete(AuthUser $authUser, Voucher $voucher): bool
    {
        return $authUser->can('Delete:Voucher');
    }

    public function restore(AuthUser $authUser, Voucher $voucher): bool
    {
        return $authUser->can('Restore:Voucher');
    }

}