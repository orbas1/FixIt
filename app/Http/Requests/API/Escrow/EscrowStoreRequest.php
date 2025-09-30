<?php

namespace App\Http\Requests\API\Escrow;

use Illuminate\Foundation\Http\FormRequest;

class EscrowStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'service_request_id' => ['required', 'integer', 'exists:service_requests,id'],
            'provider_id' => ['required', 'integer', 'exists:users,id'],
            'amount' => ['required', 'numeric', 'min:0.5'],
            'currency' => ['required', 'string', 'size:3'],
        ];
    }
}
