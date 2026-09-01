<?php

namespace App\Http\Resources\Api;

use App\Models\PartnerBill;
use Illuminate\Http\Request;

/** @mixin PartnerBill */
class PartnerBillResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        $status = $this->status;
        $statusValue = $status instanceof \BackedEnum ? $status->value : (string) $status;
        $arrivalPhoto = $this->getFirstMedia('arrival_photo') ?: null;
        $arrivalPhotoUrl = $arrivalPhoto ? $arrivalPhoto->getUrl() : null;
        $arrivalPhotoCreateTime = $arrivalPhoto ? $arrivalPhoto->created_at->format('H:i - d-m-Y') : null;

        return [
            'id' => $this->id,
            'code' => $this->code,
            'address' => $this->address,
            'date' => optional($this->date)->toDateString(),
            'start_time' => optional($this->start_time)->format('H:i:s'),
            'end_time' => optional($this->end_time)->format('H:i:s'),
            'final_total' => $this->final_total,
            'note' => $this->note,
            'requires_invoice' => $this->requires_invoice,
            'accessories' => PartnerBillAccessoryResource::collection($this->whenLoaded('accessories')),
            'status' => $statusValue,
            'thread_id' => $this->thread_id,
            'booking_photos' => $this->mediaUrls('booking_photos'),
            'arrival_photo' => $arrivalPhotoUrl,
            'arrival_photo_create_time' => $arrivalPhotoCreateTime,
            'category_name' => $this->whenLoaded('category', function () {
                return $this->category->name;
            }),
            // 'parent_category_name' => $this->whenLoaded('category', function () {
            //     return $this->category->parent->name;
            // }),
            'category_image' => $this->whenLoaded('category', function () {
                return $this->category->getFirstMediaUrl('images', 'thumb');
            }),

            'event_name' => $this->custom_event ?? $this->whenLoaded('event', fn () => $this->event->name),

            'applicant_count' => $this->whenLoaded('details', fn () => PartnerBillDetailResource::collection($this->details)->count()),

            'voucher' => $this->whenLoaded('voucher', function () {
                return [
                    'id' => $this->voucher->id,
                    'code' => $this->voucher->code,
                ];
            }),
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
