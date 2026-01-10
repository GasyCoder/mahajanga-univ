<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrganizationPageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->title,
            'content' => $this->content,
            'page_type' => $this->page_type,
            'order' => $this->order,
            'is_published' => $this->is_published,
            
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            
            'featured_image' => $this->whenLoaded('featuredImage', fn() => [
                'id' => $this->featuredImage->id,
                'url' => $this->featuredImage->url,
            ]),
            
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
