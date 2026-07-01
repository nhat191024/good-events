<?php

namespace App\Jobs;

use App\Enum\PartnerBillStatus;
use App\Models\PartnerBill;
use App\Services\PartnerBillJobScheduler;
use App\Services\PartnerBillNotificationService;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class PartnerBillFirstJob implements ShouldQueue
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
            PartnerBillStatus::CONFIRMED => $this->handleConfirmedBill($this->partnerBill, $notificationService, $scheduler),
            PartnerBillStatus::IN_JOB => $scheduler->scheduleCompletionReminder($this->partnerBill),
            default => null,
        };
    }

    /**
     * Handle a confirmed bill when the first scheduled check runs.
     */
    private function handleConfirmedBill(
        PartnerBill $partnerBill,
        PartnerBillNotificationService $notificationService,
        PartnerBillJobScheduler $scheduler,
    ): void
    {
        if ($scheduler->shouldWaitForUpcomingReminder($partnerBill)) {
            $scheduler->scheduleFirstCheck($partnerBill);

            return;
        }

        if ($scheduler->shouldSendUpcomingReminder($partnerBill)) {
            $notificationService->sendUpcomingEventReminder($partnerBill);
        }
    }
}
