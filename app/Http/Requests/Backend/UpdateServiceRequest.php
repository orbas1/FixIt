<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;

class UpdateServiceRequest extends FormRequest
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
            'required_servicemen' => 'required|numeric',
            'user_id' => 'required',
            'price' => 'required_if:type,fixed,provider_site,remotely',
            'type' => 'required',
            'service_rate' => 'required',
            'duration' => 'required',
            'duration_unit' => 'required',
            'service_id' => 'array|required_if:is_random_related_services,0',
            'per_serviceman_commission' => 'required|numeric|between:0,100',
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'The Provider field is required',
            'type' => 'Please select a service type',
            'price.required_if' => 'The price field is required',
        ];
    }
}
