<?php

namespace App\Http\Resources\Api;

use App\Enum\FileProductBillStatus;
use Illuminate\Http\Request;

/** @mixin \App\Models\FileProductBill */
class FileProductBillResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        $statusEnum = $this->status instanceof FileProductBillStatus
            ? $this->status
            : FileProductBillStatus::tryFrom((string) $this->status);

        return [
            'id' => $this->id,
            'file_product_id' => $this->file_product_id,
            'client_id' => $this->client_id,
            'total' => $this->total,
            'tax' => $this->tax,
            'final_total' => $this->final_total,
            'status' => $statusEnum?->value ?? (string) $this->status,
            'status_label' => $statusEnum?->label() ?? ucfirst((string) $this->status),
            'payment_method' => $this->payment_method?->value ?? (string) $this->payment_method,
            'created_at' => optional($this->created_at)->toIso8601String(),
            'updated_at' => optional($this->updated_at)->toIso8601String(),
            'can_repay' => $statusEnum === FileProductBillStatus::PENDING,
            'can_download' => $statusEnum === FileProductBillStatus::PAID,
            'file_product' => $this->whenLoaded('fileProduct', fn () => new FileProductResource($this->fileProduct)),
        ];
    }
}
