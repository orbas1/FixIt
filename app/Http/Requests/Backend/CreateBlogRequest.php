<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;

class CreateBlogRequest extends FormRequest
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
        return [
            'title' => ['required', 'string', 'max:255', 'unique:blogs,title,NULL,id,deleted_at,NULL'],
            'description' => ['nullable', 'min:10'],
            'content' => ['required', 'min:10'],
            'categories*' => ['nullable', 'required', 'exists:categories,id,deleted_at,NULL'],
            'tags*' => ['nullable', 'exists:tags,id,deleted_at,NULL'],
            // 'is_featured' => ['nullable', 'min:0', 'max:1'],
            'status' => ['nullable', 'required', 'min:0', 'max:1'],
            'meta_description' => ['required', 'string'],
        ];
    }

    public function messages()
    {
        return [
            'categories.required' => __('validation.blog_categories_required'),
        ];
    }
}
