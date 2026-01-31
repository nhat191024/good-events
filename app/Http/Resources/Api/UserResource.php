<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;

/** @mixin \App\Models\User */
class UserResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        $partnerProfile = $this->whenLoaded('partnerProfile');

        $avatarUrl = $this->avatar_url;
        if ($avatarUrl && str_contains($avatarUrl, 'ui-avatars.com')) {
            // If a format query exists, replace it with svg; otherwise append ?format=svg or &format=svg
            if (str_contains($avatarUrl, 'format=')) {
                $avatarUrl = preg_replace('/([&?])format=[^&]*/', '$1format=png', $avatarUrl);
            } else {
                $separator = str_contains($avatarUrl, '?') ? '&' : '?';
                $avatarUrl .= $separator . 'format=png';
            }
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'country_code' => $this->country_code,
            'bio' => $this->bio,
            'avatar' => $avatarUrl,
            'email_verified_at' => optional($this->email_verified_at)->toIso8601String(),
            'created_at' => optional($this->created_at)->toIso8601String(),
            'updated_at' => optional($this->updated_at)->toIso8601String(),
            'partner_profile' => $partnerProfile ? new PartnerProfileResource($partnerProfile) : null,
        ];
    }
}
