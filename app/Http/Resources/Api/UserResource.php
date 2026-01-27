<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;

/** @mixin \App\Models\User */
class UserResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        $partnerProfile = $this->whenLoaded('partnerProfile');
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'country_code' => $this->country_code,
            'bio' => $this->bio,
            'avatar' => $this->avatar_url,
            'email_verified_at' => optional($this->email_verified_at)->toIso8601String(),
            'created_at' => optional($this->created_at)->toIso8601String(),
            'updated_at' => optional($this->updated_at)->toIso8601String(),
            'partner_profile' => $partnerProfile ? new PartnerProfileResource($partnerProfile) : null,
        ];
    }
}
