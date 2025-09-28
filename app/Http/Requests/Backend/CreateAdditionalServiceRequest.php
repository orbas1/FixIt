<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;

class CreateAdditionalServiceRequest extends FormRequest
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
            'thumbnail' => 'required|mimetypes:image/jpeg,image/png|max:2048',
            'title' => 'required|string|max:255',
            'price' => 'required|numeric|min:1',
            'parent_id' => 'required|exists:services,id',
        ];
    }
}
