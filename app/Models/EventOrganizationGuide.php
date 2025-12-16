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
 * @property-read \App\Models\User $author
 * @property-read \App\Models\Category $category
 * @property-read \App\Models\Location|null $location
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \Spatie\MediaLibrary\MediaCollections\Models\Media> $media
 * @property-read int|null $media_count
 * @property \Illuminate\Database\Eloquent\Collection<int, \Spatie\Tags\Tag> $tags
 * @property-read int|null $tags_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventOrganizationGuide newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventOrganizationGuide newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventOrganizationGuide onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventOrganizationGuide query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventOrganizationGuide whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventOrganizationGuide whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventOrganizationGuide whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventOrganizationGuide whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventOrganizationGuide whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventOrganizationGuide whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventOrganizationGuide whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventOrganizationGuide whereLocationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventOrganizationGuide whereLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventOrganizationGuide whereMaxPeople($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventOrganizationGuide whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventOrganizationGuide whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventOrganizationGuide whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventOrganizationGuide whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventOrganizationGuide whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventOrganizationGuide whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventOrganizationGuide whereVideoUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventOrganizationGuide withAllTags(\ArrayAccess|\Spatie\Tags\Tag|array|string $tags, ?string $type = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventOrganizationGuide withAllTagsOfAnyType($tags)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventOrganizationGuide withAnyTags(\ArrayAccess|\Spatie\Tags\Tag|array|string $tags, ?string $type = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventOrganizationGuide withAnyTagsOfAnyType($tags)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventOrganizationGuide withAnyTagsOfType(array|string $type)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventOrganizationGuide withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventOrganizationGuide withoutTags(\ArrayAccess|\Spatie\Tags\Tag|array|string $tags, ?string $type = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventOrganizationGuide withoutTrashed()
 * @mixin \Eloquent
 */
class EventOrganizationGuide extends Blog
{
    protected $table = 'blogs';
}
