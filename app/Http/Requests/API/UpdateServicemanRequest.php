<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateServicemanRequest extends FormRequest
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
            'name' => 'sometimes|required|max:255|string',
            'email' => ['required', Rule::unique('users')->ignore($this->serviceman)],
            'provider_id' => 'sometimes|required|exists:users,id,deleted_at,NULL',
            'address_id' => 'sometimes|required|exists:addresses,id',
            'password' => 'nullable|string|min:8',
            'confirm_password' => 'nullable|same:password',
            'image' => 'nullable|mimes:png,jpg',
            'country_id' => 'sometimes|required|exists:countries,id',
            'state_id' => 'sometimes|required|exists:states,id',
            'city' => 'sometimes|required|string',
            'address' => 'sometimes|required',
            'postal_code' => 'sometimes|required',
            'experience_interval' => 'required',
            'experience_duration' => 'required',
        ];
    }
}
