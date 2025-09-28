<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAddressRequest extends FormRequest
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
            'type' => 'sometimes|required|string',
            'country_id' => 'sometimes|required|exists:countries,id',
            'state_id' => 'sometimes|required|exists:states,id',
            'city' => 'sometimes|required|string',
            'alternative_phone' => 'sometimes',
            'category' => ['sometimes', 'required', 'string'],
            'alternative_name' => ['sometimes', 'required', 'string'],
            'service_id' => 'sometimes|exists:services,id',
            'user_id' => 'sometimes|exists:users,id',
            'postal_code' => 'required|string',
        ];
    }
}
