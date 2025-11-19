<?php

namespace App\Http\Resources\Home;

use App\Helper\TemporaryImage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

/** @mixin \App\Models\Blog */
class BlogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $expireAt = now()->addDay();
        $plainContent = Str::of(strip_tags((string) $this->content))->squish();

        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'type' => $this->type,
            'excerpt' => $plainContent->limit(140)->toString(),
            'published_at' => optional($this->created_at)->toIso8601String(),
            'published_human' => optional($this->created_at)->translatedFormat('d M Y'),
            'thumbnail' => TemporaryImage::getTemporaryImageUrl($this, $expireAt, 'thumbnail'),
            'video_url' => $this->video_url,
            'author' => $this->whenLoaded('author', fn () => [
                'id' => $this->author->id,
                'name' => $this->author->name,
            ]),
            'category' => $this->whenLoaded('category', function () {
                $category = $this->category;

                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'parent' => $this->when(
                        $category->relationLoaded('parent') && $category->parent,
                        fn () => [
                            'id' => $category->parent->id,
                            'name' => $category->parent->name,
                        ]
                    ),
                ];
            }),
            'tags' => $this->whenLoaded('tags', function () use ($request) {
                return TagResource::collection($this->tags)->resolve($request);
            }),
        ];
    }
}
