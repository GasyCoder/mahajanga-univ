<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreNewsletterSubscriberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['SuperAdmin','Validateur']) ?? false;
    }

    public function rules(): array
    {
        return [
            'email' => ['required','email','max:255'],
            'name' => ['nullable','string','max:255'],
            'status' => ['nullable','in:active,unsubscribed,pending'],
        ];
    }
}
