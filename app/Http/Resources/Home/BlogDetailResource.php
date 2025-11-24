<?php

namespace App\Http\Resources\Home;

use App\Helper\TemporaryImage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

/** @mixin \App\Models\Blog */
class BlogDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $expireAt = now()->addDay();
        $plainContent = Str::of(strip_tags((string) $this->content))->squish();
        $wordCount = str_word_count($plainContent->toString());

        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'content' => $this->content,
            'type' => $this->type,
            'video_url' => $this->video_url,
            'excerpt' => $plainContent->limit(160)->toString(),
            'published_at' => optional($this->created_at)->toIso8601String(),
            'published_human' => optional($this->created_at)->translatedFormat('d M Y'),
            'read_time_minutes' => max(1, (int) ceil($wordCount / 200)),
            'thumbnail' => $this->getFirstMediaUrl('thumbnail'),
            'address' => $this->address,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'max_people' => $this->max_people,
            'author' => $this->whenLoaded('author', fn() => [
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
                        fn() => [
                            'id' => $category->parent->id,
                            'name' => $category->parent->name,
                        ]
                    ),
                ];
            }),
            'location' => $this->whenLoaded('location', function () {
                $location = $this->location;

                return [
                    'id' => $location->id,
                    'name' => $location->name,
                    'type' => $location->type,
                    'province' => $this->when(
                        $location->relationLoaded('province') && $location->province,
                        fn() => [
                            'id' => $location->province->id,
                            'name' => $location->province->name,
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
