<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DocumentResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,

            'status' => $this->status,
            'published_at' => $this->published_at?->toISOString(),
            'download_count' => (int) $this->download_count,

            'category' => $this->whenLoaded('category', fn() => new DocumentCategoryResource($this->category)),
            'file' => $this->whenLoaded('file', fn() => new MediaResource($this->file)),
            
            // Flattened file info for table display
            'file_type' => $this->file?->mime,
            'file_size' => $this->file?->size,
            'downloads_count' => (int) $this->download_count,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
