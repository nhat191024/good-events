<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;

/** @mixin \App\Models\Blog */
class BlogResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'type' => $this->type,
            'published_at' => optional($this->created_at)->toIso8601String(),
            'published_human' => optional($this->created_at)->translatedFormat('d M Y'),
            'max_people' => $this->max_people,
            'address' => $this->address,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'thumbnail' => $this->mediaUrl('thumbnail'),
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
            'location' => $this->whenLoaded('location', function () {
                $location = $this->location;

                return [
                    'id' => $location->id,
                    'name' => $location->name,
                    'type' => $location->type,
                    'province' => $this->when(
                        $location->relationLoaded('province') && $location->province,
                        fn () => [
                            'id' => $location->province->id,
                            'name' => $location->province->name,
                        ]
                    ),
                ];
            }),
            'tags' => $this->whenLoaded('tags', fn () => TagResource::collection($this->tags)),
        ];
    }
}
