<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Spatie\Tags\HasTags;

use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

/**
 * @property int $id
 * @property int $category_id
 * @property string $title
 * @property string $content
 * @property int $user_id
 * @property string $slug
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $author
 * @property-read \App\Models\Category $category
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Blog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Blog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Blog onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Blog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Blog whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Blog whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Blog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Blog whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Blog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Blog whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Blog whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Blog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Blog whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Blog withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Blog withoutTrashed()
 * @mixin \Eloquent
 */
class Blog extends Model implements HasMedia
{
    use SoftDeletes, HasTags, HasSlug, InteractsWithMedia, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'content',
        'user_id',
        'category_id',
        'slug',
        'video_url',
        'location_id',
    ];

    /**
     * Get the options for generating the slug.
     */
    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug');
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

    //model relationships
    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
