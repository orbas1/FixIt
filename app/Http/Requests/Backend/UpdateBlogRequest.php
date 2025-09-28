<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBlogRequest extends FormRequest
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
        $id = $this->route('blog') ? $this->route('blog')->id : $this->blog->id;

        return [
            'title' => ['nullable', 'max:255', 'unique:blogs,title,'.$id.',id,deleted_at,NULL'],
            'categories*' => ['nullable', 'exists:categories,id,deleted_at,NULL'],
            'tags*' => ['nullable', 'exists:tags,id,deleted_at,NULL'],
            // 'is_featured' => ['min:0', 'max:1'],
            'status' => ['min:0', 'max:1'],
        ];
    }

    public function messages()
    {
        return [
            'categories.required' => 'The categories field is required',
        ];
    }
}
