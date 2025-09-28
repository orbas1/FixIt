<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;

class UpdateServicePackageRequest extends FormRequest
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
            'title' => 'required|max:255',
            'provider_id' => 'required|exists:users,id',
            // 'service_id' => 'required|exists:services,id',
            'price' => 'required|numeric',
            'start_end_date' => 'required',
        ];
    }

    public function messages(): array
    {
        return [
            'provider_id' => 'Please Select Provider',
            'service_id.required' => 'The Services field is required',
            'start_end_date.required' => 'The Start Date & End Date field is required',
            'image.required' => 'Require at least one image',
        ];
    }
}
