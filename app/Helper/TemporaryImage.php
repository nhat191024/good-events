<?php

namespace App\Helper;

use Illuminate\Http\Resources\Json\JsonResource;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class TemporaryImage extends JsonResource
{
    /**
     * Get image helper for Spatie media library. If fetching a temporary url fails, fall back to the standard stored url.
     *
     * @param mixed $model
     * @param mixed $expireAt
     * @param string $collectionName
     * @param string|null $conversionName
     * @return string|null
     */
    public static function getTemporaryImageUrl($model, $expireAt, $collectionName = 'images', ?string $conversionName = null): string | null
    {
        $conversion = $conversionName ?? '';

        if ($model instanceof Media) {
            try {
                return $model->getTemporaryUrl($expireAt, $conversion);
            } catch (\Throwable $e) {
                return $model->getUrl($conversion);
            }
        }

        if (method_exists($model, 'getFirstTemporaryUrl')) {
            try {
                return $model->getFirstTemporaryUrl($expireAt, $collectionName, $conversion);
            } catch (\Throwable $e) {
                try {
                    return $conversion
                        ? $model->getFirstMediaUrl($collectionName, $conversion)
                        : $model->getFirstMediaUrl($collectionName);
                } catch (\Throwable $th) {
                    return null;
                }
            }
        }

        return null;
    }
}
