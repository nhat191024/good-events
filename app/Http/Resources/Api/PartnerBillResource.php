<?php

namespace App\Http\Resources\Api;

use App\Models\Voucher;
use Illuminate\Http\Request;

/** @mixin \App\Models\PartnerBill */
class PartnerBillResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        $status = $this->status;
        $statusValue = $status instanceof \BackedEnum ? $status->value : (string) $status;

        return [
            'id' => $this->id,
            'code' => $this->code,
            'address' => $this->address,
            'date' => optional($this->date)->toDateString(),
            'start_time' => optional($this->start_time)->format('H:i:s'),
            'end_time' => optional($this->end_time)->format('H:i:s'),
            'final_total' => $this->final_total,
            'note' => $this->note,
            'status' => $statusValue,
            'thread_id' => $this->thread_id,
            'category_name' => $this->whenLoaded('category', function () {
                return $this->category->name;
            }),
            'parent_category_name' => $this->whenLoaded('category', function () {
                return $this->category->parent->name;
            }),
            'category_image' => $this->whenLoaded('category', function () {
                return $this->category->getFirstMediaUrl('images', 'thumb');
            }),
            
            'event_name' => $this->custom_event ?? $this->whenLoaded('event', fn () => $this->event->name),
            
            'applicant_count' => $this->whenLoaded('details', fn () => PartnerBillDetailResource::collection($this->details)->count()),
        ];
    }
}
