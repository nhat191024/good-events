<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $category_id
 * @property string $title
 * @property string|null $content
 * @property int $user_id
 * @property string $slug
 * @property string|null $video_url
 * @property int|null $location_id
 * @property int|null $max_people
 * @property string $type
 * @property int $order
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activities
 * @property-read int|null $activities_count
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \Spatie\MediaLibrary\MediaCollections\Models\Media> $media
 * @property-read int|null $media_count
 * @property \Illuminate\Database\Eloquent\Collection<int, \Spatie\Tags\Tag> $tags
 * @property-read int|null $tags_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GoodLocation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GoodLocation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GoodLocation onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GoodLocation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GoodLocation whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GoodLocation whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GoodLocation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GoodLocation whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GoodLocation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GoodLocation whereLocationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GoodLocation whereMaxPeople($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GoodLocation whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GoodLocation whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GoodLocation whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GoodLocation whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GoodLocation whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GoodLocation whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GoodLocation whereVideoUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GoodLocation withAllTags(\ArrayAccess|\Spatie\Tags\Tag|array|string $tags, ?string $type = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GoodLocation withAllTagsOfAnyType($tags)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GoodLocation withAnyTags(\ArrayAccess|\Spatie\Tags\Tag|array|string $tags, ?string $type = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GoodLocation withAnyTagsOfAnyType($tags)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GoodLocation withAnyTagsOfType(array|string $type)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GoodLocation withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GoodLocation withoutTags(\ArrayAccess|\Spatie\Tags\Tag|array|string $tags, ?string $type = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GoodLocation withoutTrashed()
 * @mixin \Eloquent
 */
class GoodLocation extends Blog
{
    protected $table = 'blogs';
}
