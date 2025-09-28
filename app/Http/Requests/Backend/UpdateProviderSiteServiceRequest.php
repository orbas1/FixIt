<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProviderSiteServiceRequest extends FormRequest
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
            'provider_id' => 'required|exists:users,id',
            'price' => 'required_if:type,fixed',
            'service_type' => 'required',
            'description' => 'required',
            'service_rate' => 'required',
            'duration' => 'required',
            'duration_unit' => 'required',
            'service_id' => 'array|required_if:is_random_related_services,0',
        ];
    } 

    public function messages(): array
    {
        return [
            'provider_id.required' => 'The Provider field is required',
            'type' => 'Please select a service type',
            'price.required_if' => 'The price field is required',
        ];
    }
}
