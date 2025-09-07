<?php

namespace App\Models;

use App\Enum\PartnerServiceStatus;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PartnerService extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'category_id',
        'user_id',
        'location_id',
        'status',
    ];

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

    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    public function media()
    {
        return $this->hasMany(PartnerMedia::class, 'partner_service_id');
    }
}
