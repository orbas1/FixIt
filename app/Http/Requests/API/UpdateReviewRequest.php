<?php

namespace App\Http\Requests\API;

use App\Exceptions\ExceptionHandler;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class UpdateReviewRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'service_id' => ['nullable', 'exists:services,id,deleted_at,NULL'],
            'provider_id' => ['nullable', 'exists:users,id,deleted_at,NULL'],
            'serviceman_id' => ['nullable', 'exists:users,id,deleted_at,NULL'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'description' => ['nullable', 'string'],
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new ExceptionHandler($validator->errors()->first(), 422);
    }
}
