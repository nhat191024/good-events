<?php

namespace App\Console\Commands;

use App\Models\PartnerBill;
use App\Models\Event;
use App\Models\User;

use App\Events\NewPartnerBillCreated;

use Illuminate\Console\Command;

use App\Enum\PartnerBillStatus;

class PartnerBillTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:partner-bill-test {--count=1 : Number of partner bills to create}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create multiple partner bills and trigger the NewPartnerBillCreated event';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count = (int) $this->option('count');

        if ($count <= 0) {
            $this->error('Count must be a positive integer.');
            return 1;
        }

        // Fetch all event IDs and partner category IDs for user ID 2
        $events = Event::all()->pluck('id')->toArray();
        $PartnerCategoryIds = User::find(2)->partnerServices()->pluck('category_id')->toArray();

        if (empty($events)) {
            $this->error('No events found in the database.');
            return 1;
        }

        if (empty($PartnerCategoryIds)) {
            $this->error('No partner categories found for user ID 2.');
            return 1;
        }

        // Debug: Show available category IDs
        $this->info('Available category IDs: ' . implode(', ', $PartnerCategoryIds));

        $this->info("Creating {$count} partner bill(s)...");

        $createdBills = [];

        // Create multiple partner bills
        for ($i = 0; $i < $count; $i++) {
            $bill = PartnerBill::create([
                'code' => 'PB' . rand(100000, 999999),
                'address' => fake()->address(),
                'phone' => fake()->phoneNumber(),
                'date' => now()->addDays(rand(0, 30)),
                'start_time' => now()->addHours(rand(1, 24)),
                'end_time' => now()->addHours(rand(25, 48)),
                'final_total' => fake()->randomFloat(2, 50, 500),
                'event_id' => fake()->randomElement($events),
                'client_id' => 3,
                'partner_id' => null,
                'category_id' => fake()->randomElement($PartnerCategoryIds),
                'note' => fake()->sentence(),
                'status' => PartnerBillStatus::PENDING,
            ]);

            $createdBills[] = $bill->code;

            // Trigger the event
            // NewPartnerBillCreated::dispatch($bill);

            $this->line("Created partner bill: {$bill->code}");
        }

        $this->info("Successfully created {$count} partner bill(s):");
        foreach ($createdBills as $code) {
            $this->line("- {$code}");
        }

        return 0;
    }
}
