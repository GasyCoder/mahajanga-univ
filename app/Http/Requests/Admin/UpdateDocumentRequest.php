<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDocumentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['SuperAdmin','Redacteur','Validateur']) ?? false;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes','string','max:255'],
            'description' => ['nullable','string'],
            'document_category_id' => ['sometimes','exists:document_categories,id'],
            'file_id' => ['nullable','exists:media,id'],
            'file' => ['nullable','file','max:51200'],
            'is_public' => ['sometimes','boolean'],
            'status' => ['sometimes','string','in:draft,published,archived'],
        ];
    }
}
