<?php

namespace App\Console\Commands;

use App\Enum\CacheKey;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ClearPartnerServicesCache extends Command
{
    protected $signature = 'app:clear-partner-services-cache
                            {--user= : Clear cache for a specific user ID only}';

    protected $description = 'Clear the partner_services cache tag (fixes nested-array whereIn error on the Partner sidebar)';

    public function handle(): int
    {
        $userId = $this->option('user');

        if ($userId !== null) {
            $userId = (int) $userId;

            if (! User::where('id', $userId)->exists()) {
                $this->error("User with ID {$userId} not found.");
                return self::FAILURE;
            }

            Cache::tags([CacheKey::PARTNER_SERVICES->value])->forget(
                CacheKey::PARTNER_SERVICES->value . "_user_{$userId}"
            );
            Cache::tags([CacheKey::PARTNER_SERVICES->value])->forget(
                CacheKey::PARTNER_SERVICES->value . "_category_ids_user_{$userId}"
            );

            $this->info("Partner services cache cleared for user ID {$userId}.");

            return self::SUCCESS;
        }

        Cache::tags([CacheKey::PARTNER_SERVICES->value])->flush();

        $this->info('All partner_services cache entries cleared.');

        return self::SUCCESS;
    }
}
