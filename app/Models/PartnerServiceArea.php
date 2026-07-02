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
            Cache::tags([CacheKey::PARTNER_SERVICE_AREAS->value])->flush();
        });

        static::deleted(function (PartnerServiceArea $partnerServiceArea): void {
            Cache::tags([CacheKey::PARTNER_SERVICE_AREAS->value])->flush();
        });
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
