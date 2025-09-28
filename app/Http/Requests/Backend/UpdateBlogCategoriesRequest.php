<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBlogCategoriesRequest extends FormRequest
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
        $id = $this->route('backend.blog-category.update') ? $this->route('backend.blog-category.update')->blog_category : $this->blog_category;

        return [
            'title' => 'max:255', 'unique:categories,title,'.$id.',id,deleted_at,NULL',
            'description' => ['nullable', 'string'],
            'parent_id' => ['nullable', 'exists:categories,id,deleted_at,NULL'],
            'category_type' => ['required'],
        ];
    }
}
