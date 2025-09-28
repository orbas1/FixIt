<?php

namespace App\Http\Requests\API;

use App\Exceptions\ExceptionHandler;
use App\Rules\LatitudeLongitude;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAddressRequest extends FormRequest
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
     * @return array
     */
    public function rules()
    {
        return [
            'user_id' => ['nullable', 'exists:users,id'],
            'country_id' => 'exists:countries,id',
            'state_id' => 'exists:states,id',
            'city' => 'string',
            'latitude' => [new LatitudeLongitude],
            'longitude' => [new LatitudeLongitude],
            'type' => ['required', 'string'],
            'alternative_phone' => ['required_if:role_type,service'],
            'alternative_name' => ['required_if:role_type,service'],
        ];
    }

     /**
     * Get the custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'latitude.latitude_longitude' => __('validation.custom.latitude.latitude_longitude'),
            'longitude.latitude_longitude' => __('validation.custom.longitude.latitude_longitude'),
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new ExceptionHandler($validator->errors()->first(), 422);
    }
}
