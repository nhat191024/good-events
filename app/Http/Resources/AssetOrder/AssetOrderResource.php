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
            'file_product' => $this->whenLoaded('fileProduct', function () use ($expireAt, $statusEnum) {
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
                        'download_url' => $this->when($statusEnum === FileProductBillStatus::PAID, function () use ($expireAt, $statusEnum) {
                            return route('client-orders.asset.download', ['bill' => $this->id]);
                        }),
                        'download_urls' => $this->when($statusEnum === FileProductBillStatus::PAID, function () use ($statusEnum) {
                            $medias = $this->fileProduct->getMedia('designs');
                            $urls = [];
                            foreach ($medias as $media) {
                                $disk = $media->disk;
                                $path = $media->getPath();
                                try {
                                    $diskRoot = \Illuminate\Support\Facades\Storage::disk($disk)->path('');
                                    if (!empty($diskRoot) && str_starts_with($path, $diskRoot)) {
                                        $path = ltrim(str_replace($diskRoot, '', $path), '/\\');
                                    }
                                } catch (\Throwable $ex) {
                                    // ignore
                                }

                                if ($disk === 's3') {
                                    try {
                                        $urls[] = \Illuminate\Support\Facades\Storage::disk($disk)->temporaryUrl($path, now()->addMinutes(10));
                                        continue;
                                    } catch (\Throwable $ex) {
                                        // fallback
                                    }
                                }

                                $urls[] = route('client-orders.asset.download', ['bill' => $this->id]) . '?media_id=' . $media->id;
                            }

                            return $urls;
                        }),
                        'download_zip_url' => $this->when($statusEnum === FileProductBillStatus::PAID, function () {
                            return route('client-orders.asset.downloadZip', ['bill' => $this->id]);
                        }),
                ];
            }),
        ];
    }
}

