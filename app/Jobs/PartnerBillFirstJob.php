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
            PartnerBillStatus::PENDING => $this->expireBillWithoutPartnerDetails($this->partnerBill, $notificationService, $scheduler),
            PartnerBillStatus::CONFIRMED => $this->handleConfirmedBill($this->partnerBill, $notificationService, $scheduler),
            PartnerBillStatus::IN_JOB => $scheduler->scheduleCompletionReminder($this->partnerBill),
            default => null,
        };
    }

    /**
     * Expire a pending bill close to the event time if no partner has accepted it.
     */
    private function expireBillWithoutPartnerDetails(
        PartnerBill $partnerBill,
        PartnerBillNotificationService $notificationService,
        PartnerBillJobScheduler $scheduler,
    ): void
    {
        if ($partnerBill->details()->exists()) {
            return;
        }

        $reminderTime = $scheduler->upcomingReminderAt($partnerBill);
        $eventStartsAt = $scheduler->eventStartsAt($partnerBill);

        if (! $reminderTime || ! $eventStartsAt) {
            return;
        }

        if ($partnerBill->created_at && $partnerBill->created_at->greaterThan($reminderTime) && $eventStartsAt->isFuture()) {
            self::dispatch($partnerBill)->delay($eventStartsAt);

            return;
        }

        $partnerBill->status = PartnerBillStatus::EXPIRED;
        $partnerBill->save();

        $notificationService->sendOrderExpiredNotification($partnerBill);
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
