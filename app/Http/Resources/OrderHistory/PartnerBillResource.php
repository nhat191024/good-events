<?php

namespace App\Http\Resources\OrderHistory;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\PartnerBill */
class PartnerBillResource extends JsonResource {
    public function toArray(Request $request) {
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
            "category" => $this->whenLoaded('category', function() {
                $cat = $this->category;
                return [
                    'id' => $cat->id,
                    'name' => $cat->name,
                    'parent' => $this->when(
                        $cat->relationLoaded('parent') && $cat->parent,
                        fn () => [
                            'id' => $cat->parent->id,
                            'name' => $cat->parent->name,
                        ]
                    ),
                    'media' => $cat->getMedia('images')->map(function ($m) {
                        return [
                            'id' => $m->id,
                            'file_name' => $m->file_name,
                            'mime_type' => $m->mime_type,
                            'url' => $m->getFullUrl(),
                            // 'name' => $m->name,
                            // 'size' => $m->size,
                            // 'created_at' => optional($m->created_at)->toDateTimeString(),
                        ];
                    })->values(),
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
    //
}
