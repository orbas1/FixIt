<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
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
        $commonRules = [
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore(auth()->user()->id)],
            'phone' => ['required', 'max:255', Rule::unique('users', 'phone')->ignore(auth()->user()->id)],
        ];

        if (! auth()->user()->hasRole('admin')) {
            $commonRules = array_merge($commonRules, [
                'country_id' => 'required|exists:countries,id',
                'state_id' => 'required|exists:states,id',
                'city' => 'required|string',
                'area' => 'required|string',
                'postal_code' => 'required|string',
            ]);
        }

        return $commonRules;
    }
}
