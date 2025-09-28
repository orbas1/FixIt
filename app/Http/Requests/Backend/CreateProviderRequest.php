<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;

class CreateProviderRequest extends FormRequest
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
        $provider = [
            'type' => 'required|string|in:company,freelancer',
            'name' => 'required|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,deleted_at,id',
            'phone' => 'required|unique:users,phone,deleted_at,id',
            'experience_interval' => 'required|string',
            'experience_duration' => 'required|numeric',
            'password' => 'required|string|min:8',
            'confirm_password' => 'required|same:password',
            'bank_name' => 'required|string',
            'holder_name' => 'required|string',
            'account_number' => 'required|string',
            'branch_name' => 'required|string',
            'ifsc_code' => 'nullable|string',
            'swift_code' => 'required_if:type,company|string',
            'alternative_name' => 'max:255',
            'country_id' => 'required|exists:countries,id',
            'state_id' => 'required|exists:states,id',
            'city' => 'required|string',
            'postal_code' => 'required',
            'zones*' => ['nullable', 'required', 'exists:zones,id,deleted_at,NULL'],
        ];

         if ($this->input('type') === 'company') {
            $provider = array_merge($provider, [
                'company_name' => 'required_if:type,company',
                'company_logo' => 'required_if:type,company',
                'company_email' => 'required_if:type,company|unique:companies,email,deleted_at,id',
                'company_code' => 'required_if:type,company',
                'company_phone' => 'required_if:type,company|unique:companies,phone,deleted_at,id',
            ]);
        }

        return $provider;
    }
}
