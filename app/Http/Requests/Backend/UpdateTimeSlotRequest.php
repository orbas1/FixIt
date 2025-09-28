<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTimeSlotRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'provider_id' => 'required|exists:users,id',
            // 'gap' => 'required|integer',
            // 'time_unit' => 'required|string',
            // 'time_slots' => 'required|array',
            // 'time_slots.*.day' => 'required|string',
            // 'time_slots.*.start_time' => 'required|date_format:H:i',
            // 'time_slots.*.end_time' => 'required|date_format:H:i',
            // 'time_slots.*.status' => 'required|boolean',
        ];
    }

    public function messages()
    {
        return [
            'provider_id.required' => 'The provider field is required.',
            'provider_id.exists' => 'The selected provider is invalid.',
            'gap.required' => 'The gap field is required.',
            'gap.integer' => 'The gap must be an integer.',
            'gap.min' => 'The gap must be at least 1.',
            'time_unit.required' => 'The time unit field is required.',
            'time_unit.in' => 'The selected time unit is invalid.',
            'time_slots.required' => 'At least one time slot is required.',
            'time_slots.*.day.required' => 'The day field is required.',
            'time_slots.*.day.in' => 'The selected day is invalid.',
            'time_slots.*.start_time.required' => 'The start time field is required.',
            'time_slots.*.start_time.date_format' => 'The start time does not match the format H:i.',
            'time_slots.*.end_time.required' => 'The end time field is required.',
            'time_slots.*.end_time.date_format' => 'The end time does not match the format H:i.',
            'time_slots.*.end_time.after' => 'The end time must be after the start time.',
        ];
    }
}
