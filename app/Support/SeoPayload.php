<?php

namespace App\Support;

use RalphJSmit\Laravel\SEO\Support\SEOData;
use RalphJSmit\Laravel\SEO\TagManager;

class SeoPayload
{
    /**
     * Normalize SEOData to an array consumable by the shared SeoHead component.
     */
    public static function toArray(SEOData $seoData, TagManager $tagManager): array
    {
        $seoData = $tagManager->fillSEOData($seoData);

        return [
            'title' => $seoData->title,
            'description' => $seoData->description,
            'canonical' => $seoData->canonical_url ?? $seoData->url,
            'robots' => $seoData->robots,
            'keywords' => $seoData->tags ?? [],
            'open_graph' => [
                'title' => $seoData->openGraphTitle ?? $seoData->title,
                'description' => $seoData->description,
                'url' => $seoData->url,
                'type' => $seoData->type,
                'site_name' => $seoData->site_name,
                'image' => $seoData->image,
            ],
            'twitter' => [
                'card' => 'summary_large_image',
                'title' => $seoData->title,
                'description' => $seoData->description,
                'image' => $seoData->image,
            ],
            'json_ld' => null,
        ];
    }
}
