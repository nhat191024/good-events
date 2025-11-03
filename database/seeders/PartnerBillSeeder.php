<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Arr;
use Carbon\Carbon;

use App\Models\User;
use App\Models\Event;
use App\Models\PartnerBill;
use App\Models\PartnerCategory;
use App\Enum\Role;
use App\Enum\PartnerBillStatus;

class PartnerBillSeeder extends Seeder
{
    public function run(): void
    {
        if (PartnerBill::count() > 0) {
            $this->command->info('Partner bills already exist. Skipping PartnerBillSeeder.');
            return;
        }

        $partners = User::role(Role::PARTNER->value)->get();
        $clients = User::role(Role::CLIENT->value)->get();
        $events  = Event::all();
        $categories = PartnerCategory::all();

        if ($partners->isEmpty() || $clients->isEmpty() || $events->isEmpty() || $categories->isEmpty()) {
            $this->command->warn('Not enough base data (partners/clients/events/categories) to seed partner bills.');
            return;
        }

        $this->command->info('Seeding partner bills with mixed statuses...');

        // create ~30 bills
        $total = 30;
        for ($i = 0; $i < $total; $i++) {
            $partner = $partners->random();
            $client  = $clients->random();
            // ensure client != partner
            if ($client->id === $partner->id) {
                $client = $clients->where('id', '!=', $partner->id)->random();
            }

            $event   = $events->random();
            // prefer category from partner's services if available
            $partnerCategoryIds = $partner->partnerServices()->pluck('category_id')->all();
            $categoryId = !empty($partnerCategoryIds)
                ? Arr::random($partnerCategoryIds)
                : $categories->random()->id;

            $date = Carbon::now()->subDays(rand(0, 60));
            $start = (clone $date)->setTime(rand(8, 14), [0, 30][rand(0, 1)]);
            $end   = (clone $start)->addHours(rand(2, 6));
            $amount = rand(5, 40) * 100000; // 500k - 4M

            // Create first with pending to allow status-change hooks to fire later
            $bill = PartnerBill::create([
                'address' => '123 Test St, District '.rand(1, 12),
                'phone' => '+84'.rand(900000000, 999999999),
                'date' => $date,
                'start_time' => $start,
                'end_time' => $end,
                'final_total' => $amount,
                'event_id' => $event->id,
                'client_id' => $client->id,
                'partner_id' => $partner->id,
                'category_id' => $categoryId,
                'note' => 'Auto-seeded bill',
                'status' => PartnerBillStatus::PENDING->value,
            ]);

            // decide outcome: 50% paid, 20% cancelled, 30% stay pending
            $roll = rand(1, 100);
            if ($roll <= 50) {
                $bill->update(['status' => PartnerBillStatus::COMPLETED->value]);
            } elseif ($roll <= 70) {
                $bill->update(['status' => PartnerBillStatus::CANCELLED->value]);
            }
        }

        $this->command->info('Partner bills seeding completed.');
    }
}
