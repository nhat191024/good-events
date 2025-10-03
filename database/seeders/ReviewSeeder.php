<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

use App\Models\User;
use App\Models\PartnerCategory;
use App\Enum\Role;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        // Use the package Review model via FQCN
        $reviewModel = new \Codebyray\ReviewRateable\Models\Review();

        $clients = User::role(Role::CLIENT->value)->get();
        $partners = User::role(Role::PARTNER->value)->get();
        $categories = PartnerCategory::all();

        if ($clients->isEmpty() || ($partners->isEmpty() && $categories->isEmpty())) {
            $this->command->warn('Not enough base data to seed reviews.');
            return;
        }

        $this->command->info('Seeding reviews and ratings...');

        $total = 40;
        for ($i = 0; $i < $total; $i++) {
            $author = $clients->random();

            // 70% review a partner, 30% review a partner category
            $reviewPartner = rand(1, 100) <= 70 && $partners->isNotEmpty();

            if ($reviewPartner) {
                $subject = $partners->random();
                $reviewableType = get_class($subject); // App\Models\User
                $reviewableId = $subject->id;
            } else {
                $subject = $categories->random();
                $reviewableType = get_class($subject); // App\Models\PartnerCategory
                $reviewableId = $subject->id;
            }

            // Create the review row
            /** @var \Codebyray\ReviewRateable\Models\Review $review */
            $review = $reviewModel->newQuery()->create([
                'reviewable_type' => $reviewableType,
                'reviewable_id' => $reviewableId,
                'user_id' => $author->id,
                'department' => Arr::random(['default', 'wedding', 'event', 'studio']),
                'review' => fake()->boolean(85) ? fake()->sentences(rand(1, 3), true) : null,
                'recommend' => fake()->boolean(70),
                'approved' => true,
            ]);

            // Ratings: always include overall 1..5; optionally other dimensions
            $overall = rand(3, 5);
            DB::table('ratings')->insert([
                'review_id' => $review->id,
                'key' => 'overall',
                'value' => $overall,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach (['service', 'quality', 'value'] as $key) {
                if (fake()->boolean(60)) {
                    DB::table('ratings')->insert([
                        'review_id' => $review->id,
                        'key' => $key,
                        'value' => rand(3, 5),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        $this->command->info('Reviews seeding completed.');
    }
}
