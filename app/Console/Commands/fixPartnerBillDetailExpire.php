<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\PartnerBill;
use App\Enum\PartnerBillStatus;
use App\Enum\PartnerBillDetailStatus;

class fixPartnerBillDetailExpire extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fix-partner-bill-detail-expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix partner bill detail expiration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $expiredBills = PartnerBill::where('status', PartnerBillStatus::EXPIRED)->get();

        $this->info("Found " . $expiredBills->count() . " expired bills. Updating their details to CANCELLED...");

        foreach ($expiredBills as $bill) {
            $this->info("Updating details for bill ID: {$bill->id}");

            $bill->details()->update(['status' => PartnerBillDetailStatus::CANCELLED]);
        }

        $this->info("Finished updating partner bill details.");
    }
}
