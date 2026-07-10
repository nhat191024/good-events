<?php

namespace App\Models;

use App\Enum\CacheKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class PartnerServiceArea extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'location_id',
    ];

    protected static function booted(): void
    {
        static::saved(function (PartnerServiceArea $partnerServiceArea): void {
            self::forgetRelatedCaches($partnerServiceArea);
        });

        static::deleted(function (PartnerServiceArea $partnerServiceArea): void {
            self::forgetRelatedCaches($partnerServiceArea);
        });
    }

    private static function forgetRelatedCaches(PartnerServiceArea $partnerServiceArea): void
    {
        $originalUserId = (int) ($partnerServiceArea->getOriginal('user_id') ?: $partnerServiceArea->user_id);
        $originalLocationId = (int) ($partnerServiceArea->getOriginal('location_id') ?: $partnerServiceArea->location_id);
        $currentUserId = (int) $partnerServiceArea->user_id;
        $currentLocationId = (int) $partnerServiceArea->location_id;
        $serviceAreaCache = Cache::tags([CacheKey::PARTNER_SERVICE_AREAS->value]);

        foreach (array_unique([$originalUserId, $currentUserId]) as $userId) {
            $serviceAreaCache->forget(CacheKey::PARTNER_SERVICE_AREAS->value . "_{$userId}");
            $serviceAreaCache->forget(CacheKey::PARTNER_SERVICE_AREAS->value . "_dashboard_user_{$userId}");
        }

        foreach (array_unique([$originalLocationId, $currentLocationId]) as $locationId) {
            $serviceAreaCache->forget(CacheKey::PARTNER_SERVICE_AREAS->value . "_location_{$locationId}");
        }
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }
}
