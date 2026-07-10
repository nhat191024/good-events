<?php

namespace App\Services;

use App\Enum\CacheKey;
use App\Models\PartnerBill;
use App\Models\PartnerService;
use App\Models\PartnerServiceArea;
use Illuminate\Support\Facades\Cache;

class PartnerBillRecipientResolver
{
    /**
     * @return list<int>
     */
    public function eligiblePartnerIds(PartnerBill $partnerBill): array
    {
        if (!$partnerBill->category_id) {
            return [];
        }

        $approvedPartnerIds = $this->approvedPartnerIdsForCategory((int) $partnerBill->category_id);

        if (empty($approvedPartnerIds)) {
            return [];
        }

        $targetAreaPartnerIds = $this->partnerIdsWithoutServiceAreas($approvedPartnerIds);

        if ($partnerBill->location_id) {
            $targetAreaPartnerIds = array_values(array_unique([
                ...$targetAreaPartnerIds,
                ...$this->partnerIdsServingLocation((int) $partnerBill->location_id),
            ]));
        }

        return array_values(array_intersect($approvedPartnerIds, $targetAreaPartnerIds));
    }

    /**
     * @return list<int>
     */
    private function approvedPartnerIdsForCategory(int $categoryId): array
    {
        return Cache::tags([CacheKey::PARTNER_SERVICES->value])
            ->rememberForever(CacheKey::PARTNER_SERVICES->value . "_approved_category_{$categoryId}", function () use ($categoryId): array {
                return PartnerService::query()
                    ->where('category_id', $categoryId)
                    ->where('status', 'approved')
                    ->pluck('user_id')
                    ->map(fn ($userId): int => (int) $userId)
                    ->unique()
                    ->values()
                    ->all();
            });
    }

    /**
     * @param list<int> $partnerIds
     * @return list<int>
     */
    private function partnerIdsWithoutServiceAreas(array $partnerIds): array
    {
        $partnerIdsWithServiceAreas = PartnerServiceArea::query()
            ->whereIn('user_id', $partnerIds)
            ->pluck('user_id')
            ->map(fn ($userId): int => (int) $userId)
            ->unique()
            ->all();

        return array_values(array_diff($partnerIds, $partnerIdsWithServiceAreas));
    }

    /**
     * @return list<int>
     */
    private function partnerIdsServingLocation(int $locationId): array
    {
        return Cache::tags([CacheKey::PARTNER_SERVICE_AREAS->value])
            ->rememberForever(CacheKey::PARTNER_SERVICE_AREAS->value . "_location_{$locationId}", function () use ($locationId): array {
                return PartnerServiceArea::query()
                    ->where('location_id', $locationId)
                    ->pluck('user_id')
                    ->map(fn ($userId): int => (int) $userId)
                    ->unique()
                    ->values()
                    ->all();
            });
    }
}
