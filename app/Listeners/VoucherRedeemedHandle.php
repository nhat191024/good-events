<?php

namespace App\Listeners;

use BeyondCode\Vouchers\Events\VoucherRedeemed;

use App\Models\Voucher;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class VoucherRedeemedHandle
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(VoucherRedeemed $event): void
    {
        $voucherId = $event->voucher->id;
        $voucher = Voucher::find($voucherId);
        $voucher->data = array_merge($voucher->data->toArray(), [
            'times_used' => $voucher->timesUsed() + 1,
        ]);
        $voucher->save();
        $voucher->refresh();
    }
}
