<?php

namespace App\Http\Requests\Backend;

use App\Enums\RoleEnum;
use Illuminate\Foundation\Http\FormRequest;

class CreateServicemanWithdrawRequest extends FormRequest
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
        $roleName = auth()->user()->roles->first()->name;
        $withdrawRequest = [
            'payment_type' => 'required|in:paypal,bank',
            'message' => 'required|string',
            'amount' => 'required|numeric',
        ];

        if ($roleName == RoleEnum::SERVICEMAN) {
            return array_merge($withdrawRequest, ['provider_id' => ['exists:users,id,deleted_at,NULL']]);
        }

        return $withdrawRequest;
    }

    public function messages()
    {
        return [
            'payment_type.in' => __('validation.payment_type_in'),
        ];
    }
}
