<?php

namespace Modules\Coupon\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Request;

class CreateCouponRequest extends FormRequest
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
       
        $coupon = [
            'code' => ['required', 'min:5', 'max:20', 'unique:coupons,code,NULL,id,deleted_at,NULL'],
            'type' => ['in:fixed,percentage'],
            'min_spend' => ['required', 'numeric', 'min:0'],
            'is_unlimited' => ['min:0', 'max:1'],
            'usage_per_coupon' => ['nullable', 'numeric'],
            'usage_per_customer' => ['nullable', 'numeric'],
            'status' => ['min:0', 'max:1'],
            'is_expired' => ['min:0', 'max:1'],
            'is_first_order' => ['min:0', 'max:1'],
            'start_end_date' => ['required_if:is_expired,,==,1'],
            'is_apply_all' => ['min:0', 'max:1'],
            // 'services' => ['array'],
            'services*' => ['exists:services,id,deleted_at,NULL'],
            'users*' => ['exists:users,id,deleted_at,NULL'],
            'exclude_services' => ['nullable', 'array'],
            'users' => ['nullable', 'array'],
            'exclude_services*' => ['exists:services,id,deleted_at,NULL'],
        ];

        if (Request::input('type') == 'percentage') {
            return array_merge($coupon, ['percentage_amount' => ['required', 'regex:/^([0-9]{1,2}){1}(\.[0-9]{1,2})?$/']]);
        }

        return $coupon;
    }

    public function messages()
    {
        return [
            'percentage_amount.regex' => 'Enter price percentage between 0 to 99.99',
            'type.in' => 'Coupon type can be free shipping or fixed or percentage',
            'services.required_if' => 'Include services field is required.',
        ];
    }
}
