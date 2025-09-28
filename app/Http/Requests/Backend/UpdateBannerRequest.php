<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBannerRequest extends FormRequest
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
        $banner = [
            'type' => ['required'],
            'related_id' => ['required'],
        ];

        return $banner;
    }

    public function messages()
    {
        return [
            'type.required' => 'Please select Banner Type.',
            'related_id.required' => 'Please select Banner Category Type.',
        ];
    }
}
