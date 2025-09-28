<?php

namespace App\Http\Requests\Backend;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProviderRequest extends FormRequest
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
        $company = User::findOrFail($this->provider->id)->company;
        return [
            'type' => 'required',
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($this->provider->id),
            ],
            'code' => 'required',
            'phone' => [
                'required',
                'max:255',
                Rule::unique('users', 'phone')->ignore($this->provider->id),
            ],
            'company_name' => 'required_if:type,company|nullable|string',
            'company_email' => ['required_if:type,company', 'max:255', Rule::unique('companies', 'email')->ignore($company->id ?? null)],
            'company_code' => 'required_if:type,company|nullable',
            'company_phone' => ['nullable', 'digits_between:6,15', 'required_if:type,company', Rule::unique('companies', 'phone')->ignore($company->id ?? null)],
            'experience_interval' => 'required|string',
            'experience_duration' => 'required',
            'bank_name' => 'required|string',
            'holder_name' => 'required|string',
            'account_number' => 'numeric',
            'branch_name' => 'required|string',
            'ifsc_code' => 'string',
            'swift_code' => 'required|string',
            'zones*' => ['nullable', 'exists:zones,id,deleted_at,NULL'],
        ];
    }
}
