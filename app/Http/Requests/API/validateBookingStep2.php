<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;

class validateBookingStep2 extends FormRequest
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
            'service_id' => ['required', 'exists:services,id', 'integer'],
            'required_servicemen' => ['required', 'integer'],
        ];
    }
}
