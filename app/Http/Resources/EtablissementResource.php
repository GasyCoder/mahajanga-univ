<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EtablissementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'acronym' => $this->acronym,
            'description' => $this->description,
            
            'director_name' => $this->director_name,
            'director_title' => $this->director_title,
            
            'address' => $this->address,
            'phone' => $this->phone,
            'email' => $this->email,
            'website' => $this->website,
            
            'facebook' => $this->facebook,
            'twitter' => $this->twitter,
            'linkedin' => $this->linkedin,
            
            'logo' => $this->whenLoaded('logo', fn() => [
                'id' => $this->logo->id,
                'url' => $this->logo->url,
            ]),
            'cover_image' => $this->whenLoaded('coverImage', fn() => [
                'id' => $this->coverImage->id,
                'url' => $this->coverImage->url,
            ]),
            
            'order' => $this->order,
            'is_active' => $this->is_active,
            
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
