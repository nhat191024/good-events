<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;

/** @mixin \App\Models\Voucher */
class VoucherResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'expires_at' => optional($this->expires_at)->toIso8601String(),
            'starts_at' => optional($this->startAt())->toIso8601String(),
            'discount_percent' => $this->discountPercentage(),
            'max_discount_amount' => $this->maxDiscountAmount(),
            'min_order_amount' => $this->minOrderAmount(),
            'usage_limit' => $this->usageLimit(),
            'times_used' => $this->timesUsed(),
            'is_unlimited' => $this->isUnlimited(),
            'created_at' => optional($this->created_at)->toIso8601String(),
            'updated_at' => optional($this->updated_at)->toIso8601String(),
        ];
    }
}
