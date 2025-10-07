<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Statistical;
use App\Enum\Role;
use App\Enum\StatisticType;

class StatisticalSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure each user has baseline statistics rows according to their audience
        $this->command->info('Seeding baseline statistics for users...');

        User::query()->each(function (User $user) {
            // Determine which StatisticType cases apply for this user
            $cases = StatisticType::cases();

            foreach ($cases as $case) {
                $aud = $case->audience();
                $shouldApply = false;
                if ($aud === 'both') {
                    $shouldApply = true;
                } elseif ($aud === Role::PARTNER->value) {
                    $shouldApply = $user->hasRole(Role::PARTNER);
                } elseif ($aud === Role::CLIENT->value) {
                    $shouldApply = $user->hasRole(Role::CLIENT);
                }

                if (!$shouldApply) {
                    continue;
                }

                Statistical::firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'metrics_name' => $case->value,
                    ],
                    [
                        'metrics_value' => match ($case) {
                            StatisticType::NUMBER_CUSTOMER => 0,
                            StatisticType::SATISFACTION_RATE => 0,
                            StatisticType::TOTAL_SPENT => 0,
                            StatisticType::ORDERS_PLACED => 0,
                            StatisticType::COMPLETED_ORDERS => 0,
                            StatisticType::CANCELLED_ORDERS_PERCENTAGE => 0,
                            StatisticType::REVENUE_GENERATED => 0,
                        },
                        'metadata' => null,
                    ]
                );
            }
        });
    }
}
