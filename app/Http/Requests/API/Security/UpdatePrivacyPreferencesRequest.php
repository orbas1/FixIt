<?php

namespace App\Http\Requests\API\Security;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePrivacyPreferencesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'preferences' => ['required', 'array'],
            'preferences.marketing' => ['sometimes', 'boolean'],
            'preferences.analytics' => ['sometimes', 'boolean'],
            'preferences.essential' => ['sometimes', 'boolean'],
        ];
    }
}
