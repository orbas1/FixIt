<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAdvertisementRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        $advertisement = [
            // 'type' => ['required'],
            // 'related_id' => ['required'],
        ];

        return $advertisement;
    }

    public function messages()
    {
        return [
            'type.required' => 'Please select Banner Type.',
            'related_id.required' => 'Please select Banner Category Type.',
        ];
    }
}
