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
        //check if start time is within next 6 hours
        if ($this->partnerBill->start_date->isFuture() && $this->partnerBill->start_date->diffInHours(now()) <= 6) {
            $partnerId = $this->partnerBill->partner_id;
            $clientId = $this->partnerBill->client_id;
            $partner = User::find($partnerId);
            $client = User::find($clientId);

            //send notification
            $partner->notify(
                Notification::make()
                    ->title(__('notification.partner_show_reminder_title', ['code' => $this->partnerBill->code]))
                    ->body(__('notification.partner_show_reminder_body', ['code' => $this->partnerBill->code, 'start_date' => $this->partnerBill->start_date]))
                    ->warning()
                    ->send()
            );

            //send email
            $this->mailService->sendUpcomingEventReminder($this->partnerBill);
        } else {
            $timeUntilReminder = $this->partnerBill->start_date->subHours(6);
            SendPartnerReminder::dispatch($this->partnerBill)->delay($timeUntilReminder);
            return;
        }
    }
}
