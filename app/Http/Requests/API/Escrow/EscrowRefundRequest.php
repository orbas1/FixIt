<?php

namespace App\Http\Requests\API\Escrow;

use Illuminate\Foundation\Http\FormRequest;

class EscrowRefundRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:0.01'],
            'reason' => ['nullable', 'string', 'max:500'],
        ];
    }
}
