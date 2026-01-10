<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreNewsletterCampaignRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Redacteur peut créer une campagne draft
        return $this->user()?->hasAnyRole(['SuperAdmin','Validateur','Redacteur']) ?? false;
    }

    public function rules(): array
    {
        return [
            'subject' => ['required','string','max:255'],
            'content_html' => ['required','string'],
            'content_text' => ['nullable','string'],
            'post_id' => ['nullable','integer','exists:posts,id'],
        ];
    }
}
