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
            'category' => $this->category->name,
            'client_name' => $this->client->name,
            'date' => optional($this->date)->toDateString(),
            'start_time' => optional($this->start_time)->format('H:i'),
            'end_time' => optional($this->end_time)->format('H:i'),
            'address' => $this->address,
            'final_total' => $this->final_total ?? $this->details[0]->total ?? 0,
            'updated_at' => optional($this->updated_at)->diffForHumans(),

            'event' => $this->event->name ?? $this->custom_event,
            'note' => $this->note,
            'status' => $statusValue,
            'thread_id' => $this->thread_id,
            'arrival_photo' => $this->mediaUrl('arrival_photo'),
            'voucher' => $this->whenLoaded('voucher', fn() => [
                'id' => $this->voucher?->id,
                'code' => $this->voucher?->code,
            ]),
        ];
    }
}
