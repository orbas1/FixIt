<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;

class CreatePageRequest extends FormRequest
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
     * @return array
     */
    public function rules()
    {
        return [
            'title' => 'required',
            'app_type' => 'required|in:provider,user',
            'content' => 'required',
            'meta_title' => 'required',
            'meta_image' => 'mimes:png,jpg,jpeg',
            'app_icon' => 'required|mimes:png,jpg,jpeg',
        ];
    }
}
