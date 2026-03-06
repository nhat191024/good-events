<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;

/** @mixin \App\Models\PartnerBillDetail */
class PartnerBillDetailResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        $status = $this->status;
        $statusValue = $status instanceof \BackedEnum ? $status->value : (string) $status;

        return [
            'id' => $this->id,
            'partner_bill_id' => $this->partner_bill_id,
            'partner_id' => $this->partner_id,
            'total' => $this->total,
            'status' => $statusValue,
            'created_at' => optional($this->created_at)->toIso8601String(),
            'updated_at' => optional($this->updated_at)->toIso8601String(),
            'partner' => $this->whenLoaded('partner', fn () => new UserResource($this->partner)),
        ];
    }
}
