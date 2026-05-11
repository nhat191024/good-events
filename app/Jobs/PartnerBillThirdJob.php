<?php

namespace App\Jobs;

use App\Enum\PartnerBillStatus;
use App\Models\PartnerBill;
use App\Services\PartnerBillNotificationService;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class PartnerBillThirdJob implements ShouldQueue
{
    use Queueable;

    private PartnerBill $partnerBill;
    private PartnerBillNotificationService $partnerBillNotificationService;

    /**
     * Create a new job instance.
     */
    public function __construct(PartnerBill $partnerBill)
    {
        $this->partnerBill = $partnerBill;
        $this->partnerBillNotificationService = app(PartnerBillNotificationService::class);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->partnerBill->refresh();

        match ($this->partnerBill->status) {
            PartnerBillStatus::CONFIRMED => $this->sendPartnerReminder($this->partnerBill),
            PartnerBillStatus::IN_JOB => $this->sendPartnerReminder($this->partnerBill),
            default => null,
        };
    }

    /**
     * Remind partner to follow up completed event.
     */
    private function sendPartnerReminder(PartnerBill $partnerBill)
    {
        $partnerBill->status = PartnerBillStatus::COMPLETED;
        $partnerBill->save();

        $this->partnerBillNotificationService->sendBillCompletedReminder($partnerBill);
    }
}
