<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartnerBillAccessory extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'partner_category_accessory_id',
        'name',
        'surcharge',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'surcharge' => 'float',
        ];
    }

    public function bill(): BelongsTo
    {
        return $this->belongsTo(PartnerBill::class, 'partner_bill_id');
    }

    public function categoryAccessory(): BelongsTo
    {
        return $this->belongsTo(PartnerCategoryAccessory::class, 'partner_category_accessory_id');
    }
}
