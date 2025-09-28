<?php

namespace App\Http\Requests\API;

use App\Exceptions\ExceptionHandler;
use App\Models\Service;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class CalculateCheckoutRequest extends FormRequest
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
            'consumer_id' => ['exists:users,id'],
            'services' => ['array'],
            'services.*.service_id' => ['required', 'exists:services,id'],
            'coupon' => ['nullable', 'exists:coupons,code'],
            'payment_method' => ['required'],
            'services.*.additional_services' => [
                'nullable',
                'array',
                function ($attribute, $value, $fail) {
                    foreach ($this->services as $service) {
                        $mainService = Service::find($service['service_id']);
                        if ($mainService && !empty($service['additional_services'])) {
                            $requestedIds = collect($service['additional_services'])->pluck('id')->toArray();
                            $validIds = $mainService->additionalServices->pluck('id')->toArray();
                            $invalidAdditionalServices = array_diff($requestedIds, $validIds);
                            if (count($invalidAdditionalServices) > 0) {
                                foreach ($invalidAdditionalServices as $invalidServiceId) {
                                    $fail(__('static.additional_service_invalid_with_id', ['id' => $invalidServiceId]));
                                }
                            }
                        }
                    }
                }
            ],
            'services.*.date_time' => [
                'required',
                function ($attribute, $value, $fail) {
                    try {
                        $dateTime = \Carbon\Carbon::createFromFormat('d-M-Y,h:i a', $value);
                        $now = now();
                        if ($dateTime->isSameDay($now)) {
                            if ($dateTime->lt($now->copy()->addHour())) {
                                $fail("The $attribute must be at least 1 hour from now.");
                            }
                        }
                        
                    } catch (\Exception $e) {
                        $fail("The $attribute must be a valid date and time.");
                    }
                }
            ]

        ];
    }

    public function messages()
    {
        return [
            'services.*.service_id.exists' => __('static.service_id_invalid'),
            'coupon.exists' => __('static.coupon_code_not_found', ['code' => $this->coupon]),
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new ExceptionHandler($validator->errors()->first(), 422);
    }
}
