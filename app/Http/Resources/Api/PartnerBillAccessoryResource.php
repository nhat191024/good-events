<?php

namespace App\Http\Resources\Api;

use App\Models\PartnerBillAccessory;
use Illuminate\Http\Request;

/** @mixin PartnerBillAccessory */
class PartnerBillAccessoryResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'accessory_id' => $this->partner_category_accessory_id,
            'name' => $this->name,
        ];
    }
}
