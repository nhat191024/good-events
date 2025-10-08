<?php

namespace App\Http\Resources\OrderHistory;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\PartnerBill */
class PartnerBillResource extends JsonResource {
    public function toArray(Request $request) {
        $expireAt = now()->addMinutes(60*24);

        return [
            "id"=> $this->id,
            "code"=> $this->code,
            "address"=> $this->address,
            "date"=> $this->date,
            "start_time"=> $this->start_time,
            "end_time"=> $this->end_time,
            "final_total"=> $this->final_total,
            "note"=> $this->note,
            "status"=> $this->status,
            "created_at"=> $this->created_at,
            "updated_at"=> $this->updated_at,
            "category" => $this->whenLoaded('category', function() use ($expireAt) {
                $cat = $this->category;
                return [
                    'id' => $cat->id,
                    'name' => $cat->name,
                    'max_price' => $cat->max_price,
                    'min_price' => $cat->min_price,
                    'image' => $cat->getFirstTemporaryUrl($expireAt, 'images'),
                    'parent' => $this->when(
                        $cat->relationLoaded('parent') && $cat->parent,
                        fn () => [
                            'id' => $cat->parent->id,
                            'name' => $cat->parent->name,
                            'image' => $cat->parent->getFirstTemporaryUrl($expireAt, 'images'),
                        ]
                    ),
                ];
            }),
            "event" => $this->whenLoaded("event", function () {
                $cat = $this->event;
                return [
                    "id"=> $cat->id,
                    "name"=> $cat->name
                ];
            }),
            "partners" => $this->whenLoaded("details", function () {
                return [
                    "count" => $this->details->count()
                ];
            }),
        ];
    }
}
