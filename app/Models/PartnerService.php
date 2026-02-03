<?php

namespace App\Models;

use App\Enum\PartnerServiceStatus;
use App\Enum\CacheKey;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

/**
 * @property int $id
 * @property int $category_id
 * @property int $user_id
 * @property PartnerServiceStatus $status
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activities
 * @property-read int|null $activities_count
 * @property-read \App\Models\PartnerCategory $category
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, Media> $media
 * @property-read int|null $media_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PartnerMedia> $serviceMedia
 * @property-read int|null $service_media_count
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerService newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerService newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerService onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerService query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerService whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerService whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerService whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerService whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerService whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerService whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerService whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerService withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerService withoutTrashed()
 * @mixin \Eloquent
 */
class PartnerService extends Model implements HasMedia
{
    use SoftDeletes, InteractsWithMedia, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'category_id',
        'user_id',
        'status',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => PartnerServiceStatus::class,
        ];
    }

    /**
     * The model's validation rules.
     *
     * @var array<string, mixed>
     */
    public static array $rules = [
        'category_id' => 'required|exists:partner_categories,id',
        'user_id' => 'required|exists:users,id',
        'status' => 'required|string|in:pending,approved,rejected',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saved(function ($model) {
            Cache::tags([CacheKey::PARTNER_SERVICES->value])->flush();
        });

        static::deleted(function ($model) {
            Cache::tags([CacheKey::PARTNER_SERVICES->value])->flush();
        });

        static::restored(function ($model) {
            Cache::tags([CacheKey::PARTNER_SERVICES->value])->flush();
        });
    }

    public static function getByUserCached($userId)
    {
        return Cache::tags([CacheKey::PARTNER_SERVICES->value])->rememberForever(CacheKey::PARTNER_SERVICES->value . "_user_{$userId}", function () use ($userId) {
            return static::where('user_id', $userId)
                ->with('serviceMedia')
                ->get();
        });
    }

    /**
     * Register media collections for partner service
     * @return void
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('service_images')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp']);
    }

    /**
     * Summary of registerMediaConversions
     * @param Media|null $media
     * @return void
     */
    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(300)
            ->height(300)
            ->sharpen(10);
    }

    /**
     * Summary of getActivitylogOptions
     * @return LogOptions
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnlyDirty();
    }

    //model helper methods
    public function isPending(): bool
    {
        return $this->status === PartnerServiceStatus::PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === PartnerServiceStatus::APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === PartnerServiceStatus::REJECTED;
    }

    //model relationships
    public function category()
    {
        return $this->belongsTo(PartnerCategory::class, 'category_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function serviceMedia()
    {
        return $this->hasMany(PartnerMedia::class, 'partner_service_id');
    }
}
