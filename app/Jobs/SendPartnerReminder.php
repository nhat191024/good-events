<?php

namespace App\Jobs;

use App\Models\PartnerBill;
use App\Models\User;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

use Filament\Notifications\Notification;

use App\Services\PartnerBillMailService;

class SendPartnerReminder implements ShouldQueue
{
    use Queueable;

    private PartnerBill $partnerBill;
    private PartnerBillMailService $mailService;

    /**
     * Create a new job instance.
     */
    public function __construct(PartnerBill $partnerBill)
    {
        $this->partnerBill = $partnerBill;
        $this->mailService = app(PartnerBillMailService::class);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $eventDateTime = $this->partnerBill->date->copy()
            ->setTimeFrom($this->partnerBill->start_time);

        if ($eventDateTime->isFuture() && $eventDateTime->diffInHours(now()) <= 2) {
            $partnerId = $this->partnerBill->partner_id;
            $clientId = $this->partnerBill->client_id;
            $partner = User::find($partnerId);
            $client = User::find($clientId);

            //send notification
            $partner->notify(
                Notification::make()
                    ->title(__('notification.partner_show_reminder_title', ['code' => $this->partnerBill->code]))
                    ->body(__('notification.partner_show_reminder_body', ['code' => $this->partnerBill->code, 'start_time' => $eventDateTime]))
                    ->warning()
                    ->send()
            );

            $this->mailService->sendUpcomingEventReminder($this->partnerBill);
        } else {
            $timeUntilReminder = $eventDateTime->copy()->subHours(2);
            SendPartnerReminder::dispatch($this->partnerBill)->delay($timeUntilReminder);
            return;
        }
    }
}
