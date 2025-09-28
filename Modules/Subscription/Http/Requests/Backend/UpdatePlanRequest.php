<?php

namespace Modules\Subscription\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePlanRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
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
    public function rules()
    {
        $rules = [
            'max_services' => ['required', 'integer', 'min:0'],
            'max_servicemen' => ['required', 'integer', 'min:0'],
            'max_service_packages' => ['required', 'integer', 'min:0'],
            'max_addresses' => ['required', 'integer', 'min:0'],
            'price' => ['required', 'numeric', 'min:0'],
            'duration' => ['required', Rule::in(['monthly', 'yearly'])],
            'status' => ['min:0', 'max:1'],
        ];

        if ($this->has('name')) {
            $rules['name'] = [
                'required',
                Rule::unique('plans', 'name')->ignore($this->route('plan'))->whereNull('deleted_at'),
                'string',
                'max:255',
            ];
        }

        return $rules;
    }

    /**
     * Get the validation messages that apply to the request.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'name.required' => 'Please enter a name for the plan.',
            'name.string' => 'The name must be a valid text.',
            'name.max' => 'The name should not exceed :max characters.',
            'max_services.required' => 'Please specify the maximum number of services allowed.',
            'max_services.integer' => 'The maximum services must be a whole number.',
            'max_services.min' => 'The maximum services must be at least :min.',
            'max_addresses.required' => 'Please specify the maximum number of addresses allowed.',
            'max_addresses.integer' => 'The maximum addresses must be a whole number.',
            'max_addresses.min' => 'The maximum addresses must be at least :min.',
            'max_servicemen.required' => 'Please specify the maximum number of servicemen allowed.',
            'max_servicemen.integer' => 'The maximum servicemen must be a whole number.',
            'max_servicemen.min' => 'The maximum servicemen must be at least :min.',
            'max_service_packages.required' => 'Please specify the maximum number of service packages allowed.',
            'max_service_packages.integer' => 'The maximum service package must be a whole number.',
            'max_service_packages.min' => 'The maximum service package must be at least :min.',
            'price.required' => 'Please enter the price for the plan.',
            'price.numeric' => 'The price must be a valid number.',
            'price.min' => 'The price must be at least :min.',
        ];
    }
}
