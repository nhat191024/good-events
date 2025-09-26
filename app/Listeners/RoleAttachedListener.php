<?php

namespace App\Listeners;

use App\Enum\StatisticType;
use App\Enum\Role;

use App\Models\Statistical;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

use Spatie\Permission\Events\RoleAttached;

use Illuminate\Support\Facades\Log;

class RoleAttachedListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(RoleAttached $event): void
    {
        if ($event->model->hasRole(Role::PARTNER)) {
            //create default statistics for partner
            foreach (StatisticType::forAudience(Role::PARTNER) as $statistic) {
                Statistical::create([
                    'user_id' => $event->model->id,
                    'metrics_name' => $statistic->value,
                    'metrics_value' => 0,
                    'metadata' => json_encode([]),
                ]);
            }
        } else if ($event->model->hasRole(Role::CLIENT)) {
            //create default statistics for client
            foreach (StatisticType::forAudience(Role::CLIENT) as $statistic) {
                Statistical::create([
                    'user_id' => $event->model->id,
                    'metrics_name' => $statistic->value,
                    'metrics_value' => 0,
                    'metadata' => json_encode([]),
                ]);
            }
        }
    }
}
