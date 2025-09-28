<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;

class CreateTagRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255', 'unique:tags,name,NULL,id,deleted_at,NULL'],
            'type' => ['required', 'in:blog,service'],
            'status' => ['min:0', 'max:1'],
        ];
    }

    public function messages()
    {
        return [
            'type.in' => __('validation.type_in'),
        ];
    }
}
