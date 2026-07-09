<?php

namespace App\Jobs;

use App\Enum\PartnerBillStatus;
use App\Models\PartnerBill;
use App\Services\PartnerBillJobScheduler;
use App\Services\PartnerBillNotificationService;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;

class PartnerBillThirdJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(private PartnerBill $partnerBill) {}

    /**
     * Execute the job.
     */
    public function handle(
        PartnerBillNotificationService $notificationService,
        PartnerBillJobScheduler $scheduler,
    ): void
    {
        $this->partnerBill->refresh();

        match ($this->partnerBill->status) {
            PartnerBillStatus::CONFIRMED, PartnerBillStatus::IN_JOB => $this->sendCompletionReminder(
                $this->partnerBill,
                $notificationService,
                $scheduler,
            ),
            default => null,
        };
    }

    /**
     * Remind users to complete the bill after the event should have ended.
     */
    private function sendCompletionReminder(
        PartnerBill $partnerBill,
        PartnerBillNotificationService $notificationService,
        PartnerBillJobScheduler $scheduler,
    ): void
    {
        if ($scheduler->shouldWaitForCompletionReminder($partnerBill)) {
            $scheduler->scheduleCompletionReminder($partnerBill);

            return;
        }

        if (! Cache::add("partner_bill_completion_reminder_sent_{$partnerBill->id}", true, now()->addHours(13))) {
            return;
        }

        $notificationService->sendPartnerCompletionReminder($partnerBill);
        $scheduler->scheduleAutoCompletion($partnerBill);
    }
}
