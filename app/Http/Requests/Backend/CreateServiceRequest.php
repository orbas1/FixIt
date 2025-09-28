<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;

class CreateServiceRequest extends FormRequest
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
            'category_id' => 'array|required',
            'category_id*' => 'exists:categories,id',
            'type' => 'required|in:fixed,provider_site,remotely',
            'user_id' => 'exists:users,id',
            'required_servicemen' => 'required|numeric',
            'price' => 'required',
            'duration' => 'required',
            'duration_unit' => 'required|in:hours,minutes',
            'service_id' => 'array',
            'service_id*' => 'exists:services,id',
            'per_serviceman_commission' => 'required|numeric|between:0,100',
        ];
    }

    public function messages(): array
    {
        return [
            'provider_id.required' => __('validation.provider_id_required'),
            'service_id.required_if' => __('validation.service_id_required_if'),
            'type' => __('validation.type'),
            'price.required_if' => __('validation.price_required_if'),
        ];
    }
}
