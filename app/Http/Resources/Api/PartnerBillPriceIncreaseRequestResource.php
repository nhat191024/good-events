<?php

namespace App\Http\Resources\Api;

use App\Models\PartnerBillPriceIncreaseRequest;
use Illuminate\Http\Request;

/** @mixin PartnerBillPriceIncreaseRequest */
class PartnerBillPriceIncreaseRequestResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_id' => $this->partner_bill_id,
            'partner_id' => $this->partner_id,
            'message_id' => $this->message_id,
            'original_total' => $this->original_total,
            'requested_total' => $this->requested_total,
            'reason' => $this->reason,
            'status' => $this->status->value,
            'responded_by' => $this->responded_by,
            'responded_at' => $this->responded_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
