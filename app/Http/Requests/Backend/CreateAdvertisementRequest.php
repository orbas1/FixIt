<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;

class CreateAdvertisementRequest extends FormRequest
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

        $advertisement =  [
            'type' => 'required|string',
            'provider_id' => 'required',
            'screen' => 'required',
            'start_end_date' => 'required',
            'zone' => 'required',
            'images' => [
                Rule::requiredIf(fn () => request('type') === 'banner' && request('banner_type') === 'image'),
                'array',
            ],

            'images.*' => [
                Rule::requiredIf(fn () => request('type') === 'banner' && request('banner_type') === 'image'),
                'image',
                'mimes:jpeg,png,jpg,gif,webp',
                'max:5120',
            ],
            'service_id' => [
                Rule::requiredIf(fn () => request('type') === 'service'),
            ],
            'video_link' => [
                Rule::requiredIf(fn () => request('type') === 'banner' && request('banner_type') === 'video'),
            ],
        ];
        return $advertisement;

    }


    public function messages()
    {
        return [
            'images.required' => __('validation.adverstiment_images_required'),
            'type.required' => __('validation.adverstiment_type_required'),
        ];
    }
}
