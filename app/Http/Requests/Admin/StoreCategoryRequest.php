<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['SuperAdmin', 'Validateur']) ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required','string','max:255'],
            'type' => ['nullable','string','max:50'], // default posts
            'parent_id' => ['nullable','integer','exists:categories,id'],
        ];
    }
}
