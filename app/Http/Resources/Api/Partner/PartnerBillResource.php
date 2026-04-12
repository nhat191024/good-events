<?php

namespace App\Http\Resources\Api\Partner;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\PartnerBill */
class PartnerBillResource extends JsonResource
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

            'event' => $this->event->name ?? $this->custom_event ?? 'N/A',
            'note' => $this->note,
            'status' => $statusValue,
            'thread_id' => $this->thread_id,
        ];
    }
}
