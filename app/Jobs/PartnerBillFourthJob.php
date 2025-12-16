<?php

namespace App\Jobs;

use App\Models\PartnerBill;
use App\Models\User;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

use Filament\Notifications\Notification;

use App\Services\PartnerBillMailService;

use App\Enum\PartnerBillStatus;

class PartnerBillFourthJob implements ShouldQueue
{
    use Queueable;

    private PartnerBill $partnerBill;

    /**
     * Create a new job instance.
     */
    public function __construct(PartnerBill $partnerBill)
    {
        $this->partnerBill = $partnerBill;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->partnerBill->refresh();

        match ($this->partnerBill->status) {
            PartnerBillStatus::IN_JOB => $this->completePartnerBill($this->partnerBill),
            default => null,
        };
    }

    /**
     * Complete partner bill
     */
    private function completePartnerBill(PartnerBill $partnerBill)
    {
        $partnerBill->status = PartnerBillStatus::COMPLETED;
        $partnerBill->save();

        Notification::make()
            ->title(__('partner/bill.order_completed_success'))
            ->success()
            ->sendToDatabase(User::find($partnerBill->partner_id));
    }
}
