<?php

namespace App\Http\Requests\API\Thread;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateThreadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'subject' => ['nullable', 'string', 'max:180'],
            'type' => ['required', Rule::in(['buyer_provider', 'support'])],
            'status' => ['nullable', Rule::in(['open', 'pending_support', 'closed', 'archived'])],
            'service_request_id' => ['nullable', 'integer', 'exists:service_requests,id'],
            'booking_id' => ['nullable', 'integer', 'exists:bookings,id'],
            'participants' => ['required', 'array', 'min:1'],
            'participants.*' => ['required', 'integer', 'exists:users,id'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['nullable', Rule::in(['consumer', 'provider', 'support'])],
            'notification_preferences' => ['nullable', 'array'],
        ];
    }
}
