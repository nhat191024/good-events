<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;

/** @mixin \App\Models\PartnerProfile */
class PartnerProfileResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'partner_name' => $this->partner_name,
            'identity_card_number' => $this->identity_card_number,
            'selfie_image' => $this->selfie_image,
            'front_identity_card_image' => $this->front_identity_card_image,
            'back_identity_card_image' => $this->back_identity_card_image,
            'is_legit' => $this->is_legit,
            'location_id' => $this->location_id,
            'created_at' => optional($this->created_at)->toIso8601String(),
            'updated_at' => optional($this->updated_at)->toIso8601String(),
            'location' => $this->whenLoaded('location', fn () => [
                'id' => $this->location->id,
                'name' => $this->location->name,
            ]),
        ];
    }
}
