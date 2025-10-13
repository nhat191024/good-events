<?php
// app/Listeners/HandlePartnerBillStatusChanged.php
namespace App\Listeners;

use App\Events\PartnerBillStatusChanged;
use App\Enum\PartnerBillStatus;
use App\Notifications\OrderStatusChanged;
use App\Notifications\OrderCompleted;
use App\Services\PartnerBillMailService;
use App\Services\PartnerWidgetCacheService;

class HandlePartnerBillStatusChanged
{
    public function handle(PartnerBillStatusChanged $event): void
    {
        $bill = $event->bill;
        $newStatus = $bill->status;

        $client = $bill->client;
        OrderStatusChanged::send($bill, $client, $newStatus);

        if ($bill->partner) {
            OrderStatusChanged::send($bill, $bill->partner, $newStatus);
        }

        if ($newStatus === PartnerBillStatus::COMPLETED) {
            OrderCompleted::send($bill, $client);
            
            $mailService = new PartnerBillMailService();
            $mailService->sendOrderConfirmedNotification($bill);
        }

        PartnerWidgetCacheService::clearPartnerCaches($bill->partner_id);
    }
}