<?php

namespace App\Jobs;

use App\Models\PartnerBill;
use App\Models\User;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

use Filament\Actions\Action;
use Filament\Notifications\Notification;

use App\Services\PartnerBillNotificationService;

use App\Enum\PartnerBillStatus;

class PartnerBillSecondJob implements ShouldQueue
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
    public function handle(PartnerBillNotificationService $mailService): void
    {
        $this->partnerBill->refresh();

        match ($this->partnerBill->status) {
            PartnerBillStatus::PENDING => $this->expirePartnerBill($this->partnerBill, $mailService),
            PartnerBillStatus::CONFIRMED => $this->sendPartnerReminder($this->partnerBill, $mailService),
            PartnerBillStatus::IN_JOB => $this->SetThirdJob($this->partnerBill),
            default => null,
        };
    }

    /**
     * Expire the partner bill and notify the client.
     */
    private function expirePartnerBill(PartnerBill $partnerBill, PartnerBillNotificationService $notificationService): void
    {
        $partnerBill->status = PartnerBillStatus::EXPIRED;
        $partnerBill->saveQuietly();

        $notificationService->sendOrderExpiredNotification($partnerBill);
    }

    /**
     * Send reminder to partner about upcoming event.
     */
    private function sendPartnerReminder(PartnerBill $partnerBill, PartnerBillNotificationService $notificationService)
    {
        $eventDateTime = $partnerBill->date->copy()
        ->setTimeFrom($partnerBill->start_time);

        if ($eventDateTime->isFuture() && $eventDateTime->diffInHours(now()) <= 2) {
            $notificationService->sendUpcomingEventReminder($partnerBill);

            $eventDuration = $partnerBill->start_time->diffInHours($partnerBill->end_time);
            $timeAfterEvent = $eventDateTime->copy()->addHours($eventDuration + 6);
            PartnerBillThirdJob::dispatch($partnerBill)->delay($timeAfterEvent);
        } else {
            $timeUntilReminder = $eventDateTime->copy()->subHours(2);
            self::dispatch($partnerBill)->delay($timeUntilReminder);
            return;
        }
    }

    /**
     * Set up third job to handle in-job partner bills.
     */
    private function SetThirdJob(PartnerBill $partnerBill)
    {
        $eventDateTime = $partnerBill->date->copy()
            ->setTimeFrom($partnerBill->start_time);
        $eventDuration = $partnerBill->start_time->diffInMinutes($partnerBill->end_time);
        $timeAfterEvent = $eventDateTime->copy()->addMinutes($eventDuration + 20);
        PartnerBillThirdJob::dispatch($partnerBill)->delay($timeAfterEvent);
    }
}
