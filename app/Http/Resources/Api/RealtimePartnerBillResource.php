<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\PartnerBill */
class RealtimePartnerBillResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'final_total' => $this->final_total,
            'created_at' => optional($this->created_at)->diffForHumans(),
            'client_name' => $this->client->name,
            'category_name' => $this->category->name,
            'event_name' => $this->event->name ?? $this->custom_event,
            'date' => optional($this->date)->toDateString(),
            'start_time' => optional($this->start_time)->format('H:i'),
            'end_time' => optional($this->end_time)->format('H:i'),
            'address' => $this->address,
            'phone' => $this->phone,
            'note' => $this->note,
        ];
    }
}
