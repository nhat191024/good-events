<?php
// app/Events/PartnerBillStatusChanged.php
namespace App\Events;

use App\Models\PartnerBill;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewThreadCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(public PartnerBill $bill)
    {
    }
}