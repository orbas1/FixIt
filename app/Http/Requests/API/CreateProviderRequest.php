<?php

namespace App\Http\Requests\API;

use App\Exceptions\ExceptionHandler;
use App\Rules\LatitudeLongitude;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class CreateProviderRequest extends FormRequest
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
            'name' => 'required|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'country_id' => 'required', 'exists:countries,id',
            'state_id' => 'required', 'exists:states,id',
            'city' => 'required', 'string',
            'countryCode' => 'required',
            'phone' => 'required|unique:users|min:8',
            'latitude' => ['required', new LatitudeLongitude],
            'longitude' => ['required', new LatitudeLongitude],
            'password' => 'required|string|min:8',
            'confirm_password' => 'required|same:password',
        ];

    }

    public function failedValidation(Validator $validator)
    {
        throw new ExceptionHandler($validator->errors()->first(), 422);
    }
}
