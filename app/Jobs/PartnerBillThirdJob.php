<?php

namespace App\Jobs;

use App\Enum\PartnerBillStatus;
use App\Models\Partner;
use App\Models\PartnerBill;
use App\Services\PartnerBillJobScheduler;
use App\Services\PartnerBillNotificationService;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PartnerBillThirdJob implements ShouldBeUniqueUntilProcessing, ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(private PartnerBill $partnerBill) {}

    public function uniqueId(): string
    {
        return "partner_bill_third_{$this->partnerBill->id}";
    }

    /**
     * Execute the job.
     */
    public function handle(
        PartnerBillNotificationService $notificationService,
        PartnerBillJobScheduler $scheduler,
    ): void {
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
    ): void {
        if ($scheduler->shouldWaitForCompletionReminder($partnerBill)) {
            $scheduler->scheduleCompletionReminder($partnerBill);

            return;
        }

        if (! $partnerBill->partner()->exists()) {
            return;
        }

        $partnerBill = DB::transaction(function () use ($partnerBill): ?PartnerBill {
            $lockedBill = PartnerBill::query()->lockForUpdate()->findOrFail($partnerBill->id);

            if (! in_array($lockedBill->status, [PartnerBillStatus::CONFIRMED, PartnerBillStatus::IN_JOB], true)) {
                return null;
            }

            if (! $lockedBill->completion_reminder_started_at) {
                $lockedBill->completion_reminder_started_at = now();
                $lockedBill->saveQuietly();
            }

            return $lockedBill;
        });

        if (! $partnerBill) {
            return;
        }

        if (Cache::add("partner_bill_completion_reminder_sent_{$partnerBill->id}", true, now()->addMinutes(115))) {
            $notificationService->sendPartnerCompletionReminder($partnerBill);
        }

        if ($partnerBill->completion_reminder_started_at->copy()->addDays(3)->lessThanOrEqualTo(now())) {
            $this->banPartner($partnerBill);

            return;
        }

        $scheduler->scheduleNextCompletionReminder($partnerBill);
    }

    private function banPartner(PartnerBill $partnerBill): void
    {
        DB::transaction(function () use ($partnerBill): void {
            $lockedBill = PartnerBill::query()
                ->whereKey($partnerBill->id)
                ->lockForUpdate()
                ->first();

            if (! $lockedBill || ! in_array($lockedBill->status, [PartnerBillStatus::CONFIRMED, PartnerBillStatus::IN_JOB], true)) {
                return;
            }

            $partner = Partner::query()
                ->whereKey($lockedBill->partner_id)
                ->lockForUpdate()
                ->first();

            if (! $partner) {
                return;
            }

            if (! filled($partner->ban_reason)) {
                $partner->ban_reason = 'tạm khóa tài khoản do nghi ngờ tài khoản không hoạt động đúng quy trình hoặc đối tác ảo';
                $partner->save();
            }

            $partner->delete();
        });
    }
}
