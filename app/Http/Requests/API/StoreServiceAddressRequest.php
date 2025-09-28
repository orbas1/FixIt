<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreServiceAddressRequest extends FormRequest
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
            'address_ids' => [
                'required',
                'array',
            ],
            'address_ids.*' => [
                'required',
                Rule::exists('addresses', 'id'),
            ],
        ];
    }

    public function messages()
    {
        return [
            'address_ids.*.exists' => __('validation.address_ids_exists'),
        ];
    }
}
