<?php

namespace App\Jobs;

use App\Enum\PartnerBillStatus;
use App\Models\PartnerBill;
use App\Services\PartnerBillJobScheduler;
use App\Services\PartnerBillNotificationService;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;

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

        $completedBill = DB::transaction(function () use ($partnerBill): ?PartnerBill {
            $lockedBill = PartnerBill::query()
                ->whereKey($partnerBill->id)
                ->lockForUpdate()
                ->first();

            if (! $lockedBill || ! in_array($lockedBill->status, [PartnerBillStatus::CONFIRMED, PartnerBillStatus::IN_JOB], true)) {
                return null;
            }

            $lockedBill->status = PartnerBillStatus::COMPLETED;
            $lockedBill->save();

            return $lockedBill;
        });

        if (! $completedBill) {
            return;
        }

        $notificationService->sendBillCompletedReminder($completedBill);
    }
}
