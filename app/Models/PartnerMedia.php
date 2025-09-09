<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $partner_service_id
 * @property string $name
 * @property string $url
 * @property string $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerMedia newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerMedia newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerMedia query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerMedia whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerMedia whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerMedia whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerMedia whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerMedia wherePartnerServiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerMedia whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerMedia whereUrl($value)
 * @mixin \Eloquent
 */
class PartnerMedia extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'partner_service_id',
        'name',
        'url',
        'description',
    ];
}
