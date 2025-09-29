<?php

namespace App\Http\Requests\API\Thread;

use Illuminate\Foundation\Http\FormRequest;

class MarkThreadReadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'read_at' => ['nullable', 'date'],
        ];
    }
}
