<?php

namespace App\Models;

use App\Enum\PartnerBillDetailStatus;

use Illuminate\Database\Eloquent\Model;

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
