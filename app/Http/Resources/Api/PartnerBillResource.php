<?php

namespace App\Http\Resources\Api;

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
            'phone' => $this->phone,
            'date' => optional($this->date)->toDateString(),
            'start_time' => optional($this->start_time)->format('H:i:s'),
            'end_time' => optional($this->end_time)->format('H:i:s'),
            'total' => $this->total,
            'final_total' => $this->final_total,
            'event_id' => $this->event_id,
            'custom_event' => $this->custom_event,
            'client_id' => $this->client_id,
            'partner_id' => $this->partner_id,
            'category_id' => $this->category_id,
            'note' => $this->note,
            'status' => $statusValue,
            'thread_id' => $this->thread_id,
            'arrival_photo' => $this->mediaUrl('arrival_photo'),
            'created_at' => optional($this->created_at)->toIso8601String(),
            'updated_at' => optional($this->updated_at)->toIso8601String(),
            'category' => $this->whenLoaded('category', fn () => new PartnerCategoryResource($this->category)),
            'event' => $this->whenLoaded('event', fn () => new EventResource($this->event)),
            'client' => $this->whenLoaded('client', fn () => new UserResource($this->client)),
            'partner' => $this->whenLoaded('partner', fn () => new UserResource($this->partner)),
            'details' => $this->whenLoaded('details', fn () => PartnerBillDetailResource::collection($this->details)),
            'voucher' => $this->whenLoaded('voucher', fn () => [
                'id' => $this->voucher?->id,
                'code' => $this->voucher?->code,
            ]),
        ];
    }
}
