<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;

class BecomeProviderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */

    public function rules()
    {
        $provider = [
            'type' => 'required|string|in:company,freelancer',
            'name' => 'required|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,NULL,id,deleted_at,NULL',
            'phone' => 'required|unique:users,phone,NULL,id,deleted_at,NULL',
            'password' => 'required|string|min:8',
            'confirm_password' => 'required|same:password',
            'image' => 'nullable|file',
            'zones*' => ['nullable', 'exists:zones,id,deleted_at,NULL'],
        ];

        return $provider;
    }
}
