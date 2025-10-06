<?php

namespace App\Http\Resources\OrderHistory;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\PartnerBill */
class PartnerBillHistoryResource extends JsonResource
{
    public function toArray(Request $request)
    {
        return [
            "id" => $this->id,
            "code" => $this->code,
            "address" => $this->address,
            "date" => $this->date,
            "start_time" => $this->start_time,
            "end_time" => $this->end_time,
            "final_total" => $this->final_total,
            "note" => $this->note,
            "status" => $this->status,
            "created_at" => $this->created_at,
            "category" => $this->whenLoaded('category', function () {
                $cat = $this->category;
                return [
                    'id' => $cat->id,
                    'name' => $cat->name,
                    'parent' => $this->when(
                        $cat->relationLoaded('parent') && $cat->parent,
                        fn() => [
                            'name' => $cat->parent->name,
                        ]
                    ),
                ];
            }),
            "event" => $this->whenLoaded("event", function () {
                $cat = $this->event;
                return [
                    "id" => $cat->id,
                    "name" => $cat->name
                ];
            }),
            "partner" => $this->whenLoaded("partner", function () {
                $cat = $this->partner;
                return [
                    "id" => $cat->id,
                    "name" => $cat->name, //statistics
                    'statistics' => $this->when(
                        $cat->relationLoaded('statistics') && $cat->statistics,
                        fn() => $cat->statistics
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
                        $cat->relationLoaded('partnerProfile') && $cat->partnerProfile,
                        fn() => [
                            'id' => $cat->partnerProfile->id,
                            'partner_name' => $cat->partnerProfile->partner_name,
                        ]
                    ),
                ];
            }),
        ];
    }
}
