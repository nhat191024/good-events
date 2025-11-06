<?php

namespace App\Http\Resources\OrderHistory;

use App\Helper\TemporaryImage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\PartnerBillDetail */
class PartnerBillDetailResource extends JsonResource {
    public function toArray(Request $request) {
        return [
            "id"=> $this->id,
            "total"=> $this->total,
            "status"=> $this->status,
            "created_at"=> $this->created_at,
            "updated_at"=> $this->updated_at,
            "partner" => $this->whenLoaded('partner', function() {
                $part = $this->partner;
                return [
                    'id' => $part->id,
                    'name' => $part->name,
                    'avatar' => $part->avatar,
                    'statistics' => $this->when(
                        $part->relationLoaded('statistics') && $part->statistics,
                        fn() => $part->statistics
                            //* need to add more, missing a few
                            ->whereIn('metrics_name', [
                                'average_stars',
                                'total_ratings'
                            ])
                            ->mapWithKeys(fn($stat) => [
                                $stat->metrics_name => $stat->metrics_value,
                            ])
                    ),
                    'partner_profile' => $this->when(
                        $part->relationLoaded('partnerProfile') && $part->partnerProfile,
                        fn() => [
                            'id' => $part->partnerProfile->id,
                            'partner_name' => $part->partnerProfile->partner_name,
                        ]
                    ),
                ];
            }),
        ];
    }
}
