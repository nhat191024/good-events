<?php

namespace App\Models;

use App\Enum\PartnerBillPriceIncreaseRequestStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartnerBillPriceIncreaseRequest extends Model
{
    protected $fillable = [
        'partner_bill_id',
        'partner_id',
        'message_id',
        'original_total',
        'requested_total',
        'reason',
        'status',
        'responded_by',
        'responded_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'original_total' => 'integer',
            'requested_total' => 'integer',
            'status' => PartnerBillPriceIncreaseRequestStatus::class,
            'responded_at' => 'datetime',
        ];
    }

    public function bill(): BelongsTo
    {
        return $this->belongsTo(PartnerBill::class, 'partner_bill_id');
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'partner_id');
    }

    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }

    public function responder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responded_by');
    }
}
