<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use BeyondCode\Vouchers\Traits\HasVouchers;

/**
 * @property int $id
 * @property int $user_id
 * @property string $partner_name
 * @property string|null $identity_card_number
 * @property string|null $selfie_image
 * @property string|null $front_identity_card_image
 * @property string|null $back_identity_card_image
 * @property int $is_legit
 * @property int $location_id
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activities
 * @property-read int|null $activities_count
 * @property-read \App\Models\Location $location
 * @property-read \App\Models\User $user
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \BeyondCode\Vouchers\Models\Voucher> $vouchers
 * @property-read int|null $vouchers_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerProfile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerProfile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerProfile onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerProfile query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerProfile whereBackIdentityCardImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerProfile whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerProfile whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerProfile whereFrontIdentityCardImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerProfile whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerProfile whereIdentityCardNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerProfile whereIsLegit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerProfile whereLocationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerProfile wherePartnerName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerProfile whereSelfieImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerProfile whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerProfile whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerProfile withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerProfile withoutTrashed()
 * @mixin \Eloquent
 */
class PartnerProfile extends Model
{
    use SoftDeletes, LogsActivity, HasVouchers;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'partner_name',
        'identity_card_number',
        'location_id',
        'selfie_image',
        'front_identity_card_image',
        'back_identity_card_image',
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


    //model relationship
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}
