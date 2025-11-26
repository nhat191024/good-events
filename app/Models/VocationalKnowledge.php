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
 * @property string|null $address
 * @property string|null $latitude
 * @property string|null $longitude
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
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VocationalKnowledge newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VocationalKnowledge newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VocationalKnowledge onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VocationalKnowledge query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VocationalKnowledge whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VocationalKnowledge whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VocationalKnowledge whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VocationalKnowledge whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VocationalKnowledge whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VocationalKnowledge whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VocationalKnowledge whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VocationalKnowledge whereLocationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VocationalKnowledge whereLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VocationalKnowledge whereMaxPeople($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VocationalKnowledge whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VocationalKnowledge whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VocationalKnowledge whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VocationalKnowledge whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VocationalKnowledge whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VocationalKnowledge whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VocationalKnowledge whereVideoUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VocationalKnowledge withAllTags(\ArrayAccess|\Spatie\Tags\Tag|array|string $tags, ?string $type = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VocationalKnowledge withAllTagsOfAnyType($tags)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VocationalKnowledge withAnyTags(\ArrayAccess|\Spatie\Tags\Tag|array|string $tags, ?string $type = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VocationalKnowledge withAnyTagsOfAnyType($tags)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VocationalKnowledge withAnyTagsOfType(array|string $type)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VocationalKnowledge withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VocationalKnowledge withoutTags(\ArrayAccess|\Spatie\Tags\Tag|array|string $tags, ?string $type = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VocationalKnowledge withoutTrashed()
 * @mixin \Eloquent
 */
class VocationalKnowledge extends Blog
{
    protected $table = 'blogs';
}
