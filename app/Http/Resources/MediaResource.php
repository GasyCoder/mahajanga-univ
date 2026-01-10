<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MediaResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'url' => $this->url, // via accessor
            'filename' => basename($this->path),
            'type' => $this->mime,
            'disk' => $this->disk,
            'path' => $this->path,
            'mime' => $this->mime,
            'size' => $this->size,
            'alt' => $this->alt,
            'width' => $this->width,
            'height' => $this->height,
            'uploaded_at' => $this->created_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
