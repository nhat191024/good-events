<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class PartnerWidgetCacheService
{
    /**
     * Clear all partner widget caches when data changes
     */
    public static function clearPartnerCaches(int $partnerId): void
    {
        // Clear revenue chart cache
        $revenueKey = 'partner_revenue_chart_' . $partnerId . '_' . Carbon::now()->format('Y-m-d-H');
        Cache::forget($revenueKey);

        // Clear previous hour cache as well in case it's near hour boundary
        $prevHourKey = 'partner_revenue_chart_' . $partnerId . '_' . Carbon::now()->subHour()->format('Y-m-d-H');
        Cache::forget($prevHourKey);

        // Clear statistics cache
        Cache::forget("partner_stats_{$partnerId}");

        // Clear top services cache
        Cache::forget("partner_top_services_{$partnerId}");
    }

    /**
     * Get cache keys for debugging
     */
    public static function getCacheKeys(int $partnerId): array
    {
        return [
            'revenue_chart' => 'partner_revenue_chart_' . $partnerId . '_' . Carbon::now()->format('Y-m-d-H'),
            'statistics' => "partner_stats_{$partnerId}",
            'top_services' => "partner_top_services_{$partnerId}",
        ];
    }
}
