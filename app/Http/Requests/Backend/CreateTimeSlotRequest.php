<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateTimeSlotRequest extends FormRequest
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
            'provider_id' => ['required', 'exists:users,id,deleted_at,NULL', Rule::unique('time_slots')->whereNull('deleted_at')],
            'time_slots' => 'required|array',
        ];
    }

    public function messages()
    {
        return [
            'provider_id.required' => __('validation.provider_id_required'),
            'provider_id.exists' => __('validation.provider_id_exists'),
            'gap.required' => __('validation.gap_required'),
            'gap.integer' => __('validation.gap_integer'),
            'gap.min' => __('validation.gap_min'),
            'time_unit.required' => __('validation.time_unit_required'),
            'time_unit.in' => __('validation.time_unit_in'),
            'time_slots.required' => __('validation.time_slots_required'),
            'time_slots.*.day.required' => __('validation.time_slots_day_required'),
            'time_slots.*.day.in' => __('validation.time_slots_day_in'),
            'time_slots.*.start_time.required' => __('validation.time_slots_start_time_required'),
            'time_slots.*.start_time.date_format' => __('validation.time_slots_start_time_date_format'),
            'time_slots.*.end_time.required' => __('validation.time_slots_end_time_required'),
            'time_slots.*.end_time.date_format' => __('validation.time_slots_end_time_date_format'),
            'time_slots.*.end_time.after' => __('validation.time_slots_end_time_after'),
        ];
    }
}
