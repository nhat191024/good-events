<?php

namespace App\Helper;

use Illuminate\Http\Resources\Json\JsonResource;

class TemporaryImage extends JsonResource
{
    /**
     * Get image helper for spatie media library, if cannot get the img with temporary method, returns the original file path
     * @param mixed $model
     * @param mixed $expireAt
     * @param mixed $collectionName
     * @return string|null
     */
    public static function getTemporaryImageUrl($model, $expireAt, $collectionName = 'images'): string | null
    {
        try {
            return $model->getFirstTemporaryUrl($expireAt, $collectionName);
        } catch (\Throwable $e) {
            try {
                return $model->getFirstMediaUrl($collectionName);
            } catch (\Throwable $th) {
                return null;
            }
        }
    }
}
