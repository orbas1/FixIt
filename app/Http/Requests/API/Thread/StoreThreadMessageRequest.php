<?php

namespace App\Http\Requests\API\Thread;

use Illuminate\Foundation\Http\FormRequest;

class StoreThreadMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'body' => ['nullable', 'string'],
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => ['array'],
            'attachments.*.media_id' => ['required', 'integer', 'exists:media,id'],
            'is_system' => ['sometimes', 'boolean'],
            'meta' => ['sometimes', 'array'],
        ];
    }
}
