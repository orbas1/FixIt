<?php

namespace App\Http\Requests\API\Escrow;

use Illuminate\Foundation\Http\FormRequest;

class CreatePaymentIntentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'context.source' => ['nullable', 'string', 'max:120'],
            'context.session_id' => ['nullable', 'string', 'max:120'],
        ];
    }

    public function context(): array
    {
        return $this->validated('context', []);
    }
}
