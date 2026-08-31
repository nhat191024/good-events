<?php

namespace App\Jobs;

use App\Models\PartnerBill;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class PartnerBillAutoCompleteJob implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(private PartnerBill $partnerBill) {}

    public function uniqueId(): string
    {
        return "partner_bill_auto_complete_{$this->partnerBill->id}";
    }

    /**
     * Keep legacy queued jobs harmless after automatic completion is disabled.
     */
    public function handle(): void {}
}
