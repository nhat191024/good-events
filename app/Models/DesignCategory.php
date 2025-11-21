<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $type
 * @property int $order
 * @property int|null $parent_id
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activities
 * @property-read int|null $activities_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Blog> $blogs
 * @property-read int|null $blogs_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Category> $children
 * @property-read int|null $children_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\FileProduct> $fileProducts
 * @property-read int|null $file_products_count
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \Spatie\MediaLibrary\MediaCollections\Models\Media> $media
 * @property-read int|null $media_count
 * @property-read \App\Models\Category|null $parent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\RentProduct> $rentProducts
 * @property-read int|null $rent_products_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DesignCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DesignCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DesignCategory onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DesignCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DesignCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DesignCategory whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DesignCategory whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DesignCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DesignCategory whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DesignCategory whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DesignCategory whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DesignCategory whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DesignCategory whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DesignCategory whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DesignCategory withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DesignCategory withoutTrashed()
 * @mixin \Eloquent
 */
class DesignCategory extends Category
{
    protected $table = 'categories';
}
