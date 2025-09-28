<?php

namespace Modules\Coupon\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Request;
use Illuminate\Validation\Rule;

class UpdateCouponRequest extends FormRequest
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
    public function rules()
    {
        $id = $this->route('coupon') ? $this->route('coupon') : $this->coupon->id;
        $coupon = [
            'code' => ['required', 'min:5', 'max:20', Rule::unique('coupons', 'code')->ignore($id)->whereNull('deleted_at')],
            'type' => ['required', 'in:free_service,fixed,first_order,percentage'],
            'min_spend' => ['required', 'nullable', 'numeric', 'min:0'],
            'is_unlimited' => ['min:0', 'max:1'],
            'usage_per_coupon' => ['nullable', 'numeric'],
            'usage_per_customer' => ['nullable', 'numeric'],
            'status' => ['min:0', 'max:1'],
            'is_expired' => ['min:0', 'max:1'],
            'is_first_order' => ['min:0', 'max:1'],
            'is_apply_all' => ['min:0', 'max:1'],
            'exclude_services' => ['nullable', 'array', 'exists:services,id,deleted_at,NULL'],
            'services' => ['required_if:is_apply_all,==,0', 'array', 'exists:services,id,deleted_at,NULL'],
        ];

        if (Request::input('type') == 'percentage') {

            return array_merge($coupon, ['amount' => ['required', 'regex:/^([0-9]{1,2}){1}(\.[0-9]{1,2})?$/']]);
        }
        return $coupon;
    }

    public function messages()
    {
        return [
            'amount.regex' => 'Enter amount percentage between 0 to 99.99',
            'type.in' => 'Coupon type can be free service or fixed or percentage',
        ];
    }
}
