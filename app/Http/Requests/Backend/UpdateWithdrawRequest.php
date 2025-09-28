<?php

namespace App\Http\Requests\Backend;

use App\Exceptions\ExceptionHandler;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class UpdateWithdrawRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'payment_type' => ['nullable', 'in:paypal,bank'],
            'provider_id' => ['exists:users,id,deleted_at,NULL'],
            'status' => ['nullable', 'required', 'in:pending,approved,rejected'],
        ];
    }

    public function messages()
    {
        return [
            'payment_type.in' => 'Payment type should be paypal or bank ',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new ExceptionHandler($validator->errors()->first(), 422);
    }
}
