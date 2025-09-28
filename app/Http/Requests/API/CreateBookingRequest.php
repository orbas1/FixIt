<?php

namespace App\Http\Requests\API;

use App\Exceptions\ExceptionHandler;
use App\Helpers\Helpers;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class CreateBookingRequest extends FormRequest
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
        $roleName = Helpers::getCurrentRoleName();
        $booking = [
            'services' => ['array'],
            'consumer_id' => 'exists:users,id,deleted_at,NULL',
            'services.*.service_id' => ['exists:services,id,deleted_at,NULL'],
            'coupon' => ['nullable', 'exists:coupons,code,deleted_at,NULL'],
            'services.*.select_serviceman' => 'in:as_per_my_choice,app_choose',
            'payment_method' => ['string'],
        ];

        return $booking;
    }

    public function failedValidation(Validator $validator)
    {
        throw new ExceptionHandler($validator->errors()->first(), 422);
    }
}
