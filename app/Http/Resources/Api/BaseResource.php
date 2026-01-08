<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class BaseResource extends JsonResource
{
    protected function mediaUrl(string $collection, string $conversion = 'thumb'): ?string
    {
        if (!method_exists($this->resource, 'getFirstMediaUrl')) {
            return null;
        }

        $url = $this->resource->getFirstMediaUrl($collection, $conversion);

        return $url !== '' ? $url : null;
    }

    /**
     * @return array<int, string>
     */
    protected function mediaUrls(string $collection, string $conversion = 'thumb'): array
    {
        if (!method_exists($this->resource, 'getMedia')) {
            return [];
        }

        $items = $this->resource->getMedia($collection);

        return $items->map(function ($media) use ($conversion) {
            try {
                $url = $media->getUrl($conversion);
            } catch (\Throwable $e) {
                $url = $media->getFullUrl();
            }

            return $url ?: null;
        })->filter()->values()->all();
    }
}
