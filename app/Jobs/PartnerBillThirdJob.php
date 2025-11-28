<?php

namespace App\Jobs;

use App\Models\PartnerBill;
use App\Models\User;

use Cmgmyr\Messenger\Models\Message;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

use App\Enum\PartnerBillStatus;

use App\Settings\PartnerSettings;

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
        if ($partnerBill->status !== PartnerBillStatus::IN_JOB) {
            $partnerBill->status = PartnerBillStatus::IN_JOB;
            $partnerBill->save();
        }

        $admin = User::whereName('Admin')->first();
        $partner = User::find($partnerBill->partner_id);

        Message::create([
            'thread_id' => $partnerBill->thread_id,
            'user_id' => $admin->id,
            'body' => __('notification.partner_bill_followup_reminder_body', ['code' => $partnerBill->code]),
        ]);

        $partnerSettings = app(PartnerSettings::class);
        $feePercentage = $partnerSettings->fee_percentage;
        $amount = floor($partnerBill->final_total * ($feePercentage / 100));

        $id = date('YmdHis') . rand(1000, 9999) + $partnerBill->id + rand(100, 999);
        $oldBalance = $partner->balanceInt;
        $partner->forceWithdraw($amount, [
            'reason' => "Phí nền tảng cho show mã {$partnerBill->code}",
            'transaction_codes' => $id,
            'old_balance' => $oldBalance,
            'new_balance' => $oldBalance - $amount
        ], true);

        $timeAfterOneDay = now()->addDay();
        PartnerBillFourthJob::dispatch($partnerBill)->delay($timeAfterOneDay);
    }
}
