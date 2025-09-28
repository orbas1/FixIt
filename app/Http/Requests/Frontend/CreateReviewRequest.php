<?php

namespace App\Http\Requests\Frontend;

use Illuminate\Foundation\Http\FormRequest;

class CreateReviewRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'service_id' => ['nullable','required','exists:services,id,deleted_at,NULL'],
            'provider_id' => ['nullable','required', 'exists:users,id,deleted_at,NULL'],
            'serviceman_id' => ['nullable','required', 'exists:users,id,deleted_at,NULL'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'description' => ['nullable', 'string'],
        ];
    }
}
