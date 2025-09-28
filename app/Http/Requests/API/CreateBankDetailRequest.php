<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;

class CreateBankDetailRequest extends FormRequest
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
            'bank_name' => 'required',
            'holder_name' => 'required|string',
            'account_number' => 'required',
            'branch_name' => 'required',
            'ifsc_code' => 'string',
            'swift_code' => 'string',
        ];
    }
}
