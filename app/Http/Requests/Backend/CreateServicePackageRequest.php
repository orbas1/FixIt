<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;

class CreateServicePackageRequest extends FormRequest
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
            'service_id.required' => __('validation.service_id_required'),
            'start_end_date.required' => __('validation.start_end_date_required'),
            'image.required' => __('validation.image_required'),
        ];
    }
}
