<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateBlogCategoriesRequest extends FormRequest
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
            'title' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories')->where('category_type', $this->category_type)->whereNull('deleted_at'),
            ],
            'description' => ['nullable', 'required', 'string'],
            'parent_id' => ['nullable', 'exists:categories,id,deleted_at,NULL'],
            'image' => 'nullable',
            'category_type' => 'in:blog',
        ];
    }
}
