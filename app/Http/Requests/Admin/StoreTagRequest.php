<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreTagRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['SuperAdmin', 'Validateur']) ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required','string','max:255'],
            'type' => ['nullable','string','max:50'],
            'color' => ['nullable','string','max:7'],
        ];
    }
}
