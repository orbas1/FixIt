<?php

namespace App\Http\Requests\API;

use App\Rules\LatitudeLongitude;
use Illuminate\Foundation\Http\FormRequest;

class CreateAddressRequest extends FormRequest
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
        $rules = [
            'country_id' => ['required', 'exists:countries,id'],
            'state_id' => ['nullable', 'exists:states,id'],
            'city' => ['required', 'string'],
            'address' => ['required'],
            'latitude' => ['required'],
            'longitude' => ['required'],
            'postal_code' => ['required'],
            'alternative_phone' => ['required_if:role_type,service'],
            'alternative_name' => ['required_if:role_type,service'],
        ];

        return $rules;
    }

    public function messages(): array
    {
        return [
            'latitude.latitude_longitude' => __('validation.custom.latitude.latitude_longitude'),
            'longitude.latitude_longitude' => __('validation.custom.longitude.latitude_longitude'),
        ];
    }
}
