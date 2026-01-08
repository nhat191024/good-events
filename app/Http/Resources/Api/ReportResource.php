<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;

/** @mixin \App\Models\Report */
class ReportResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        $status = $this->status;
        $statusValue = $status instanceof \BackedEnum ? $status->value : (string) $status;

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'reported_user_id' => $this->reported_user_id,
            'reported_bill_id' => $this->reported_bill_id,
            'thread_id' => $this->thread_id,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $statusValue,
            'created_at' => optional($this->created_at)->toIso8601String(),
            'updated_at' => optional($this->updated_at)->toIso8601String(),
            'user' => $this->whenLoaded('user', fn () => new UserResource($this->user)),
            'reported_user' => $this->whenLoaded('reportedUser', fn () => new UserResource($this->reportedUser)),
            'reported_bill' => $this->whenLoaded('reportedBill', fn () => new PartnerBillResource($this->reportedBill)),
        ];
    }
}
