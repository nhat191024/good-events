<?php

namespace App\Models;

use App\Enum\PartnerBillStatus;

use Illuminate\Database\Eloquent\Model;
use Cmgmyr\Messenger\Models\Thread;

class PartnerBill extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'code',
        'address',
        'phone',
        'date',
        'start_time',
        'end_time',
        'final_total',
        'client_id',
        'partner_id',
        'note',
        'status',
        'thread_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'date' => 'datetime',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'final_total' => 'float',
        'status' => PartnerBillStatus::class,
    ];

    //model helpers method
    public function isPaid(): bool
    {
        return $this->status === PartnerBillStatus::PAID;
    }

    public function isCancelled(): bool
    {
        return $this->status === PartnerBillStatus::CANCELLED;
    }

    public function isPending(): bool
    {
        return $this->status === PartnerBillStatus::PENDING;
    }

    //model relationship
    public function details()
    {
        return $this->hasMany(PartnerBillDetail::class);
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function partner()
    {
        return $this->belongsTo(User::class, 'partner_id');
    }

    public function thread()
    {
        return $this->belongsTo(Thread::class, 'thread_id');
    }
}
