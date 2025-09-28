<?php

namespace App\Http\Requests\Backend;

use App\Rules\UniqueCategoryInZone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoriesRequest extends FormRequest
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
        $categoryId = $this->route('category')->id;
        return [
            'title' => [
                'max:255',
                new UniqueCategoryInZone($this->title, $this->category_type, $this->zones ?? [], $categoryId)
            ],
            'description' => ['nullable', 'string'],
            'parent_id' => ['nullable', 'exists:categories,id,deleted_at,NULL'],
            'commission' => ['required', 'regex:/^([0-9]{1,2}){1}(\.[0-9]{1,2})?$/'],
            'category_type' => ['required'],
            'zones*' => ['nullable', 'exists:zones,id,deleted_at,NULL'],

        ];
    }

    public function messages()
    {
        return [
            'zones.required' => __('validation.zones_required'),
        ];
    }
}
