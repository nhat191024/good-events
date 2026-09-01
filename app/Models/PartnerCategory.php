<?php

namespace App\Models;

use App\Enum\CacheKey;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use RalphJSmit\Laravel\SEO\Models\SEO;
use RalphJSmit\Laravel\SEO\Support\HasSEO;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $type
 * @property string|null $video_url
 * @property int $order
 * @property int|null $parent_id
 * @property float|null $min_price
 * @property float|null $max_price
 * @property string|null $description
 * @property CarbonImmutable|null $deleted_at
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @property-read Collection<int, PartnerCategory> $children
 * @property-read int|null $children_count
 * @property-read MediaCollection<int, Media> $media
 * @property-read int|null $media_count
 * @property-read PartnerCategory|null $parent
 * @property-read Collection<int, PartnerService> $partnerServices
 * @property-read int|null $partner_services_count
 * @property-read SEO $seo
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerCategory onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerCategory whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerCategory whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerCategory whereMaxPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerCategory whereMinPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerCategory whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerCategory whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerCategory whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerCategory whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerCategory whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerCategory whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerCategory whereVideoUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerCategory withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerCategory withoutTrashed()
 *
 * @mixin \Eloquent
 */
class PartnerCategory extends Model implements HasMedia
{
    use HasSEO, HasSlug, InteractsWithMedia, LogsActivity, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'video_url', // warning: this field is not video url and actually an iframe embed code string
        'order',
        'parent_id',
        'min_price',
        'max_price',
        'description',
    ];

    /**
     * Get the options for generating the slug.
     */
    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }

    /**
     * Summary of registerMediaCollections
     */
    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('images')
            ->useDisk('public');
    }

    /**
     * Summary of registerMediaConversions
     */
    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(400)
            ->height(400)
            ->sharpen(10)
            ->withResponsiveImages()
            ->format('webp')
            ->queued();

        $this->addMediaConversion('mobile_optimized')
            ->width(300)
            ->height(300)
            ->withResponsiveImages()
            ->format('webp')
            ->queued();
    }

    /**
     * Summary of getActivitylogOptions
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnlyDirty();
    }

    // Model Boot
    protected static function boot()
    {
        parent::boot();

        static::saved(function ($model) {
            Cache::tags([CacheKey::PARTNER_CATEGORIES->value, CacheKey::PARTNER_CATEGORY_WITH_PARENT->value])->flush();
        });

        static::deleted(function ($model) {
            Cache::tags([CacheKey::PARTNER_CATEGORIES->value])->flush();

            $model->partnerServices()->delete();
        });

        static::restored(function ($model) {
            Cache::tags([CacheKey::PARTNER_CATEGORIES->value, CacheKey::PARTNER_CATEGORY_WITH_PARENT->value])->flush();

            $model->partnerServices()->restore();
        });
    }

    public static function getTree()
    {
        return Cache::tags([CacheKey::PARTNER_CATEGORIES->value])->remember(CacheKey::PARTNER_CATEGORIES_TREE->value, now()->addHours(6), function () {
            return static::with(['children' => fn ($query) => $query->orderBy('order')])->whereNull('parent_id')->orderBy('order')->get();
        });
    }

    public static function getAllCached()
    {
        return Cache::tags([CacheKey::PARTNER_CATEGORIES->value])->remember(CacheKey::PARTNER_CATEGORIES_ALL->value, now()->addHours(6), function () {
            return static::all();
        });
    }

    // model relationships
    public function parent()
    {
        return $this->belongsTo(PartnerCategory::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(PartnerCategory::class, 'parent_id');
    }

    public function partnerServices()
    {
        return $this->hasMany(PartnerService::class, 'category_id');
    }

    public function accessories(): HasMany
    {
        return $this->hasMany(PartnerCategoryAccessory::class);
    }
}
