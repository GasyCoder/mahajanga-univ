<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['SuperAdmin','Redacteur','Validateur']) ?? false;
    }

    public function rules(): array
    {
        return [
            'title' => ['required','string','max:255'],
            'excerpt' => ['nullable','string'],
            'content_html' => ['required','string'],

            'cover_image_id' => ['nullable','exists:media,id'],

            'category_ids' => ['nullable','array'],
            'category_ids.*' => ['integer','exists:categories,id'],

            'tag_ids' => ['nullable','array'],
            'tag_ids.*' => ['integer','exists:tags,id'],

            'gallery' => ['nullable','array'],
            'gallery.*.media_id' => ['required','exists:media,id'],
            'gallery.*.position' => ['nullable','integer','min:0'],
            'gallery.*.caption' => ['nullable','string','max:255'],

            'is_featured' => ['sometimes','boolean'],
            'is_pinned' => ['sometimes','boolean'],

            'seo_title' => ['nullable','string','max:255'],
            'seo_description' => ['nullable','string','max:255'],

            'status' => ['sometimes','string','in:draft,published,archived'],
            'notify_subscribers' => ['sometimes','boolean'],
        ];
    }
}
