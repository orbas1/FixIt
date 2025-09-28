<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePageRequest extends FormRequest
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
        $id = $this->route('page') ? $this->route('page') : $this->id;

        return [
            'title' => 'required', 'max:255', 'unique:pages,title,'.$id.',id,deleted_at,NULL',
            'app_icon' => 'mimes:png,jpg,jpeg',
            'meta_image' => 'mimes:png,jpg,jpeg',
            'content' => 'required',
            'meta_title' => 'required',
        ];
    }
}
