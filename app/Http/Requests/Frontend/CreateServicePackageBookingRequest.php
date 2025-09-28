<?php

namespace App\Http\Requests\Frontend;

use Illuminate\Foundation\Http\FormRequest;

class CreateServicePackageBookingRequest extends FormRequest
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
            'service_packages.services.*.address_id' => 'required|integer',
            'service_packages.services.*.required_servicemen' => 'required|integer',
            'service_packages.services.*.service_id' => 'required|integer',
            'service_packages.services.*.select_date_time' => 'required|string',
            'service_packages.services.*.date_time' => 'required|string',
        ];
    }

    public function messages()
    {
        return [
            'service_packages.services.*.address_id.required' => 'The address is required for each service.',
            'service_packages.services.*.address_id.integer' => 'The address must be a valid integer.',

            'service_packages.services.*.required_servicemen.required' => 'The number of required servicemen is required for each service.',
            'service_packages.services.*.required_servicemen.integer' => 'The number of required servicemen must be a valid integer.',

            'service_packages.services.*.service_id.required' => 'The service is required for each service.',
            'service_packages.services.*.service_id.integer' => 'The service must be a valid integer.',

            'service_packages.services.*.select_date_time.required' => 'You must select a date and time for each service.',
            'service_packages.services.*.select_date_time.string' => 'The selected date and time must be a string.',

            'service_packages.services.*.date_time.required' => 'The date and time are required for each service.',
            'service_packages.services.*.date_time.string' => 'The date and time must be a string.',
        ];
    }
}
