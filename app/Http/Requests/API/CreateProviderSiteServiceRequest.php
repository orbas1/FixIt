<?php

namespace App\Http\Requests\API;

use App\Rules\LatitudeLongitude;
use Illuminate\Foundation\Http\FormRequest;

class CreateProviderSiteServiceRequest extends FormRequest
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
        $rules = [
            'title' => 'required|max:255',
            'price' => 'required_if:type,fixed',
            'provider_id' => 'exists:users,id,deleted_at,NULL',
            'duration' => 'required|min:1',
            'discount' => 'required|integer',
            'description' => 'required|string',
            'thumbnail' => 'required',
            'duration_unit' => 'required|string',
            'category_id' => ['required', 'array', 'exists:categories,id,deleted_at,NULL'],
        ];

        return $rules;
    }
}
