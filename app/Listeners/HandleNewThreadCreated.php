<?php
// app/Listeners/HandlePartnerBillCreated.php
namespace App\Listeners;

use App\Events\NewThreadCreated;
use App\Notifications\ThreadCreated;
use App\Services\PartnerWidgetCacheService;

class HandleNewThreadCreated
{
    public function handle(NewThreadCreated $event): void
    {
        $bill = $event->bill;
        
        $client = $bill->client;
        ThreadCreated::send($bill, $client);
        
        PartnerWidgetCacheService::clearPartnerCaches($bill->partner_id);
    }
}