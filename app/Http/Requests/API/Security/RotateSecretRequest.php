<?php

namespace App\Http\Requests\API\Security;

use App\Models\Secret;
use Illuminate\Foundation\Http\FormRequest;

class RotateSecretRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Secret $secret */
        $secret = $this->route('secret');

        return $this->user()?->can('update', $secret) ?? false;
    }

    public function rules(): array
    {
        return [
            'value' => ['required'],
            'metadata' => ['array'],
        ];
    }
}
