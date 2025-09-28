<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;

class CreateServicemanRequest extends FormRequest
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
            'name' => 'required|max:255|string',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|unique:users|max:255',
            'password' => 'required|string|min:8',
            'confirm_password' => 'required|same:password',
            'experience_interval' => 'required|in:years,months',
            'experience_duration' => 'required|numeric',
            'image' => 'nullable|mimes:png,jpg,jpeg',
            'provider_id' => 'required|exists:users,id',
            'address_type' => 'required|string',
            'country_id' => 'required|exists:countries,id',
            'state_id' => 'required|exists:states,id',
            'city' => 'required|string',
            'alternative_phone' => 'numeric|nullable',
            'alternative_name' => 'string|nullable',
            'postal_code' => 'required',
        ];
    }
}
