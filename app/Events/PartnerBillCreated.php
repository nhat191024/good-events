<?php
// app/Events/PartnerBillCreated.php
namespace App\Events;

use App\Models\PartnerBill;
use Illuminate\Broadcasting\InteractsWithBroadcasting;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PartnerBillCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(public PartnerBill $bill)
    {
    }
}