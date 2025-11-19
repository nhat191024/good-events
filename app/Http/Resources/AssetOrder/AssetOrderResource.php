<?php

namespace App\Http\Resources\AssetOrder;

use App\Enum\FileProductBillStatus;
use App\Helper\TemporaryImage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\FileProductBill */
class AssetOrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $status = $this->status;
        $statusEnum = $status instanceof FileProductBillStatus
            ? $status
            : FileProductBillStatus::tryFrom((string) $status);

        $expireAt = now()->addMinutes(200 * 24);

        return [
            'id' => $this->id,
            'total' => $this->total,
            'final_total' => $this->final_total,
            'status' => $statusEnum?->value ?? (string) $status,
            'status_label' => $statusEnum?->label() ?? ucfirst((string) $status),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'can_repay' => $statusEnum === FileProductBillStatus::PENDING,
            'can_download' => $statusEnum === FileProductBillStatus::PAID,
            'file_product' => $this->whenLoaded('fileProduct', function () use ($expireAt) {
                $product = $this->fileProduct;

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'price' => $product->price,
                    'description' => $product->description,
                    'thumbnail' => TemporaryImage::getTemporaryImageUrl($product, $expireAt, 'thumbnails'),
                    'category' => $this->when(
                        $product->relationLoaded('category') && $product->category,
                        fn () => [
                            'id' => $product->category->id,
                            'name' => $product->category->name,
                            'slug' => $product->category->slug,
                        ]
                    ),
                ];
            }),
        ];
    }
}

