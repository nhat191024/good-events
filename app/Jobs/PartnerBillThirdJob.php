<?php

namespace App\Jobs;

use App\Models\PartnerBill;
use App\Models\User;

use Cmgmyr\Messenger\Models\Message;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

use App\Enum\PartnerBillStatus;

use Filament\Actions\Action;
use Filament\Notifications\Notification;

class PartnerBillThirdJob implements ShouldQueue
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

        Notification::make()
            ->title(__('partner/bill.order_completed_success'))
            ->success()
            ->actions([
                Action::make('open')
                    ->label('Mở chat')
                    ->url(route('chat.index', ['chat' => $partnerBill->thread_id])),
            ])
            ->sendToDatabase(User::find($partnerBill->partner_id));
    }
}
