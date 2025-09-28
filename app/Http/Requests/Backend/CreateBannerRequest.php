<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;

class CreateBannerRequest extends FormRequest
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
            'title' => 'required|string',
            'images' => 'required|array',
            'type' => 'required|string',
            'related_id' => 'required',
        ];

        return $banner;
    }

    public function messages()
    {
        return [
            'images.required' => __('validation.banner_images_required'),
            'type.required' => __('validation.banner_type_required'),
            'related_id.required' => __('validation.banner_related_id_required'),
        ];
    }
}
