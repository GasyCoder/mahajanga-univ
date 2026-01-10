<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StorePartnerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['SuperAdmin','Validateur']) ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required','string','max:255'],
            'type' => ['required','in:national,international'],
            'website_url' => ['nullable','url','max:255'],
            'country' => ['nullable','string','max:255'],
            'description' => ['nullable','string'],
            'is_featured' => ['sometimes','boolean'],
            'logo_id' => ['nullable','exists:media,id'],
        ];
    }
}