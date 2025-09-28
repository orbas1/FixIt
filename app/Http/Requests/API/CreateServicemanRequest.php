<?php

namespace App\Http\Requests\API;

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
            'name' => 'sometimes|required|max:255|string',
            'email' => 'required|email|unique:users,email,NULL,id,deleted_at,NULL',
            'provider_id' => 'sometimes|required|exists:users,id,deleted_at,NULL',
            'address_id' => 'sometimes|required|exists:addresses,id',
            'password' => 'nullable|string|min:8',
            'experience_duration' => 'required|string',
            'confirm_password' => 'nullable|same:password',
            'image' => 'mimes:jpg,png,jpeg',
            'identity_no' => 'sometimes|required|unique:user_documents,identity_no,NULL,id,deleted_at,NULL',
            'country_id' => 'sometimes|required|exists:countries,id',
            'state_id' => 'sometimes|required|exists:states,id',
            'city' => 'sometimes|required|string',
            'address' => 'sometimes|required',
            'area' => 'sometimes|required|string',
            'postal_code' => 'sometimes|required',
        ];
    }
}
