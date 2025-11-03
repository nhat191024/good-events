<?php
// app/Listeners/HandlePartnerBillCreated.php
namespace App\Listeners;

use App\Events\PartnerBillCreated;
use App\Notifications\OrderCreated;
use App\Services\PartnerWidgetCacheService;

class HandlePartnerBillCreated
{
    public function handle(PartnerBillCreated $event): void
    {
        $bill = $event->bill;
        
        $client = $bill->client;
        OrderCreated::send($bill, $client);
        
        PartnerWidgetCacheService::clearPartnerCaches($bill->partner_id);
    }
}