<?php

namespace App\Jobs;

use App\Enum\PartnerBillStatus;
use App\Models\PartnerBill;
use App\Services\PartnerBillNotificationService;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class PartnerBillSecondJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(private PartnerBill $partnerBill) {}

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
        $partnerBill->status = PartnerBillStatus::EXPIRED;
        $partnerBill->save();

        $notificationService->sendOrderExpiredNotification($partnerBill);
    }
}
