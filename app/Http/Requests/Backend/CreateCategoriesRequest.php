<?php

namespace App\Http\Requests\Backend;

use App\Rules\UniqueCategoryInZone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateCategoriesRequest extends FormRequest
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
                new UniqueCategoryInZone($this->title, $this->category_type, $this->zones ?? [])
            ],
            'description' => ['nullable', 'string'],
            'zones*' => ['nullable', 'required', 'exists:zones,id,deleted_at,NULL'],
            'parent_id' => ['nullable', 'exists:categories,id,deleted_at,NULL'],
            'image' => 'nullable',
            'commission' => ['required', 'regex:/^([0-9]{1,2}){1}(\.[0-9]{1,2})?$/'],
            'category_type' => ['required', 'in:service'],
        ];
    }

    public function messages()
    {
        return [
            'commission_regex' => __('validation.commission_regex'),
            'category_type' => __('validation.category_type'),
            'zones.required' => __('validation.zones_required'),
        ];
    }
}
