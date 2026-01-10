<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            'content_html' => $this->content_html,

            'status' => $this->status,
            'published_at' => $this->published_at?->toISOString(),

            'is_featured' => (bool) $this->is_featured,
            'is_pinned' => (bool) $this->is_pinned,

            'seo_title' => $this->seo_title,
            'seo_description' => $this->seo_description,

            'cover_image' => $this->whenLoaded('coverImage', fn() => new MediaResource($this->coverImage)),
            'gallery' => $this->whenLoaded('gallery', fn() => MediaResource::collection($this->gallery)),
            'categories' => $this->whenLoaded('categories', fn() => CategoryResource::collection($this->categories)),
            'tags' => $this->whenLoaded('tags', fn() => TagResource::collection($this->tags)),

            'author' => $this->whenLoaded('author', fn() => [
                'id' => $this->author->id,
                'name' => $this->author->name,
            ]),

            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}