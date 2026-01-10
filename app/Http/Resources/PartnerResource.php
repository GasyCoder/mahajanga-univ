<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PartnerResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type, // national|international
            'website_url' => $this->website_url,
            'country' => $this->country,
            'description' => $this->description,
            'is_featured' => (bool) $this->is_featured,
            'logo' => $this->whenLoaded('logo', fn() => new MediaResource($this->logo)),
        ];
    }
}