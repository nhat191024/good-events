<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Enum\PartnerBillStatus;
use App\Enum\StatisticType;
use App\Models\PartnerBill;
use App\Models\Statistical;
use App\Models\PartnerProfile;

class fixDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fix-database {--user_id= : User ID to fix statistics for a specific partner}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting database fix...');

        $userId = $this->option('user_id');

        if ($userId) {
            $partner = PartnerProfile::where('user_id', $userId)->first();
            if (!$partner) {
                $this->error("No partner found with user ID: {$userId}");
                return 1;
            }
            $partners = collect([$partner]);
        } else {
            $partners = PartnerProfile::all();
        }

        $this->info("Number of partners: " . $partners->count());

        $this->info("Start reviewing partners and recalculating statistics for those with incomplete statistics...");
        //start check and calculate the statistics for each partner that have incomplete statistics row
        foreach ($partners as $partner) {
            $this->info("Reviewing partner ID: {$partner->user_id}, Name: {$partner->partner_name}");

            //check for partner have any bill - if not, skip to next partner
            $partnerBills = PartnerBill::where('partner_id', $partner->user_id)->get();
            if ($partnerBills->isEmpty()) {
                $this->info("No bills found for partner ID: {$partner->user_id}");
                continue;
            }

            //recalculate number of customer
            $numberCustomer = $partnerBills->pluck('client_id')->unique()->count();
            Statistical::updateOrCreate(
                ['user_id' => $partner->user_id, 'metrics_name' => StatisticType::NUMBER_CUSTOMER->value],
                ['metrics_value' => $numberCustomer]
            );

            //recalculate revenue generated
            $revenueGenerated = $partnerBills->sum('final_total');
            Statistical::updateOrCreate(
                ['user_id' => $partner->user_id, 'metrics_name' => StatisticType::REVENUE_GENERATED->value],
                ['metrics_value' => $revenueGenerated]
            );

            //recalculate completed orders
            $completedOrders = $partnerBills->where('status', PartnerBillStatus::COMPLETED)->count();
            Statistical::updateOrCreate(
                ['user_id' => $partner->user_id, 'metrics_name' => StatisticType::COMPLETED_ORDERS->value],
                ['metrics_value' => $completedOrders]
            );

            //recalculate average stars, total ratings, satisfaction rate
            $metrics = Statistical::syncPartnerRatingMetrics($partner->user_id);

            $this->info("Updated statistics for partner ID: {$partner->user_id} - " . json_encode($metrics));

            //recalculate cancelled orders percentage
            $cancelledOrders = $partnerBills->where('status', PartnerBillStatus::CANCELLED)->count();
            $totalOrders = $partnerBills->count();
            $cancelledOrdersPercentage = $totalOrders > 0 ? ($cancelledOrders / $totalOrders) * 100 : 0;
            Statistical::updateOrCreate(
                ['user_id' => $partner->user_id, 'metrics_name' => StatisticType::CANCELLED_ORDERS_PERCENTAGE->value],
                ['metrics_value' => $cancelledOrdersPercentage]
            );
        }

        $this->info('Database fix completed.');
    }
}
