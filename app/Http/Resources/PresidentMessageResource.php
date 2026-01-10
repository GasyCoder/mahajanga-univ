<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PresidentMessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'intro' => $this->intro,
            'content' => $this->content,
            'president_name' => $this->president_name,
            'president_title' => $this->president_title,
            'mandate_period' => $this->mandate_period,
            'photo' => $this->whenLoaded('photo', fn() => [
                'id' => $this->photo->id,
                'url' => $this->photo->url,
            ]),
            'is_active' => $this->is_active,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
