<?php

namespace App\Models;

use App\Enum\PartnerBillDetailStatus;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $partner_bill_id
 * @property int $partner_id
 * @property float $total
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $partner
 * @property-read \App\Models\PartnerBill $partnerBill
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerBillDetail newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerBillDetail newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerBillDetail query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerBillDetail whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerBillDetail whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerBillDetail wherePartnerBillId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerBillDetail wherePartnerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerBillDetail whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerBillDetail whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerBillDetail whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class PartnerBillDetail extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'partner_bill_id',
        'partner_id',
        'total',
        'status',
    ];

    //model helpers methods
    public function isNew(): bool
    {
        return $this->status === PartnerBillDetailStatus::NEW;
    }

    public function isClosed(): bool
    {
        return $this->status === PartnerBillDetailStatus::CLOSED;
    }

    //model relationship
    public function partnerBill()
    {
        return $this->belongsTo(PartnerBill::class);
    }

    public function partner()
    {
        return $this->belongsTo(User::class, 'partner_id');
    }
}
