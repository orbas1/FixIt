<?php

namespace App\Http\Requests\API;

use App\Exceptions\ExceptionHandler;
use App\Models\User;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class CreateServiceRequest extends FormRequest
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
            'title' => 'required|max:255',
            'price' => 'required_if:type,fixed',
            'provider_id' => 'exists:users,id,deleted_at,NULL',
            'duration' => 'required|min:1',
            'discount' => 'required|integer',
            'content' => 'required',
            'thumbnail' => 'required',
            'duration_unit' => 'required|string|in:hours,minutes',
            'category_id' => ['required', 'array', 'exists:categories,id,deleted_at,NULL'],
            'per_serviceman_commission' => 'required|numeric|between:0,100',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new ExceptionHandler($validator->errors()->first(), 422);
    }

    public function withValidator(Validator $validator)
    {
        if (isset($this->provider_id)) {
            $validator->after(function ($validator) {
                $providerId = $this->input('provider_id');

                $provider = User::find($providerId);

                if (! $provider || ! $provider->hasRole('provider')) {
                    $validator->errors()->add('provider_id', __('validation.user_is_not_provider'));
                }
            });
        }
    }
}
