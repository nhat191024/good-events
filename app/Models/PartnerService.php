<?php

namespace App\Models;

use App\Enum\PartnerServiceStatus;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

/**
 * @property int $id
 * @property int $category_id
 * @property int $user_id
 * @property string $status
 * @property string|null $service_media
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activities
 * @property-read int|null $activities_count
 * @property-read \App\Models\PartnerCategory $category
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PartnerMedia> $media
 * @property-read int|null $media_count
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerService newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerService newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerService onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerService query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerService whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerService whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerService whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerService whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerService whereServiceMedia($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerService whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerService whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerService whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerService withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerService withoutTrashed()
 * @mixin \Eloquent
 */
class PartnerService extends Model
{
    use SoftDeletes, LogsActivity;

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

    public function media()
    {
        return $this->hasMany(PartnerMedia::class, 'partner_service_id');
    }
}
