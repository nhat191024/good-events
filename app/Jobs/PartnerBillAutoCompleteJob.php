<?php

namespace App\Jobs;

use App\Enum\PartnerBillStatus;
use App\Models\PartnerBill;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;

class PartnerBillAutoCompleteJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(private PartnerBill $partnerBill) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->partnerBill->refresh();

        if (! in_array($this->partnerBill->status, [PartnerBillStatus::CONFIRMED, PartnerBillStatus::IN_JOB], true)) {
            return;
        }

        DB::transaction(function (): void {
            $lockedBill = PartnerBill::query()
                ->whereKey($this->partnerBill->id)
                ->lockForUpdate()
                ->first();

            if (! $lockedBill || ! in_array($lockedBill->status, [PartnerBillStatus::CONFIRMED, PartnerBillStatus::IN_JOB], true)) {
                return;
            }

            $lockedBill->status = PartnerBillStatus::COMPLETED;
            $lockedBill->save();
        });
    }
}
