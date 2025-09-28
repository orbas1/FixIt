<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateRequestServiceRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'provider_id' => ['exists:users,id,deleted_at,NULL', 'nullable'],
            'title' => ['required', Rule::unique('service_requests')->whereNull('deleted_at'),],
            'description' => ['required','string'],
            'duration' => ['required', 'min:1'],
            'duration_unit' => ['required','string', 'in:hours,minutes'],
            'required_servicemen' => ['required', 'integer'],
            'status' => ['string'],
            'service_id' => ['exists:services,id,deleted_at,NULL','nullable'],
            'booking_date' => ['required'],
            'category_ids' => ['required','array'],
            'category_ids.*' => ['exists:categories,id'],
        ];
    }
}
