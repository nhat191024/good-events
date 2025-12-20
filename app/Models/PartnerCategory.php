<?php

namespace App\Models;

use App\Enum\CacheKey;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Support\Facades\Cache;

use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

use Spatie\Image\Enums\CropPosition;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $type
 * @property int $order
 * @property int|null $parent_id
 * @property float|null $min_price
 * @property float|null $max_price
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activities
 * @property-read int|null $activities_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, PartnerCategory> $children
 * @property-read int|null $children_count
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, Media> $media
 * @property-read int|null $media_count
 * @property-read PartnerCategory|null $parent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PartnerService> $partnerServices
 * @property-read int|null $partner_services_count
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
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerCategory withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerCategory withoutTrashed()
 * @mixin \Eloquent
 */
class PartnerCategory extends Model implements HasMedia
{
    use SoftDeletes, HasSlug, InteractsWithMedia, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
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
     * @return void
     */
    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('images')
            ->useDisk('public');
    }

    /**
     * Summary of registerMediaConversions
     * @param Media|null $media
     * @return void
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
     * @return LogOptions
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnlyDirty();
    }

    //Model Boot
    protected static function boot()
    {
        parent::boot();

        static::saved(function ($model) {
            Cache::tags([CacheKey::PARTNER_CATEGORIES])->flush();
        });

        static::deleted(function ($model) {
            Cache::tags([CacheKey::PARTNER_CATEGORIES])->flush();
        });

        static::restored(function ($model) {
            Cache::tags([CacheKey::PARTNER_CATEGORIES])->flush();
        });
    }

    public static function getTree()
    {
        return Cache::tags([CacheKey::PARTNER_CATEGORIES])->rememberForever(CacheKey::PARTNER_CATEGORIES_TREE->value, function () {
            return static::with('children')->whereNull('parent_id')->orderBy('order')->get();
        });
    }

    public static function getAllCached()
    {
        return Cache::tags([CacheKey::PARTNER_CATEGORIES])->rememberForever(CacheKey::PARTNER_CATEGORIES_ALL->value, function () {
            return static::all();
        });
    }

    //model relationships
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
}
