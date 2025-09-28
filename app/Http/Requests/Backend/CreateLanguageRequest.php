<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;

class CreateLanguageRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255', 'unique:system_langs,name,NULL,id,deleted_at,NULL'],
            'locale' => ['required', 'unique:system_langs,locale,NULL,id,deleted_at,NULL'],
            'app_locale' => ['required', 'unique:system_langs,app_locale,NULL,id,deleted_at,NULL'],
        ];
    }
}
