<?php

namespace App\Http\Requests\API\Security;

use Illuminate\Foundation\Http\FormRequest;

class StoreSecretRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', \App\Models\Secret::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'key' => ['required', 'string', 'max:190'],
            'value' => ['required'],
            'metadata' => ['array'],
        ];
    }
}
