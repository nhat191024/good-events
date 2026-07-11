<?php

namespace App\Jobs;

use App\Enum\PartnerBillStatus;
use App\Models\PartnerBill;
use App\Services\PartnerBillNotificationService;

use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class PartnerBillSecondJob implements ShouldQueue, ShouldBeUnique
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(private PartnerBill $partnerBill) {}

    public function uniqueId(): string
    {
        return "partner_bill_second_{$this->partnerBill->id}";
    }

    /**
     * Execute the job.
     */
    public function handle(PartnerBillNotificationService $notificationService): void
    {
        $this->partnerBill->refresh();

        match ($this->partnerBill->status) {
            PartnerBillStatus::PENDING => $this->expirePartnerBill($this->partnerBill, $notificationService),
            default => null,
        };
    }

    /**
     * Expire the partner bill and notify the client.
     */
    private function expirePartnerBill(PartnerBill $partnerBill, PartnerBillNotificationService $notificationService): void
    {
        if (! $partnerBill->details()->exists()) {
            return;
        }

        $partnerBill->status = PartnerBillStatus::EXPIRED;
        $partnerBill->save();

        $notificationService->sendOrderExpiredNotification($partnerBill);
    }
}
