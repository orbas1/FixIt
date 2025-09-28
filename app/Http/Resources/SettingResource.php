<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Http\Resources\Json\JsonResource;

class SettingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $settings = parent::toArray($request);
        $allowedCategories = array_column(\App\Enums\FrontSettingsEnum::cases(), 'value');
        $filteredSettings = Arr::only($settings, $allowedCategories);
        
        $values = [
            'general' => [
                'mode', 'favicon', 'copyright', 'dark_logo', 'site_name',
                'light_logo', 'platform_fees', 'default_timezone',
                'min_booking_amount', 'platform_fees_type',
                'default_currency_id', 'default_language_id'
            ],
            'activation' => [
                'coupon_enable', 'wallet_enable',
                'platform_fees_status', 'service_auto_approve',
                'provider_auto_approve',
                'maintenance_mode'
            ],
            'provider_commissions' => [
                'status' ,'default_commission_rate', 'is_category_based_commission'
            ],
            'subscription_plan' => [
                'reminder_message', 'days_before_reminder'
            ],

        ];

        foreach ($values as $category => $keys) {
            if (isset($filteredSettings[$category])) {
                if (empty($keys)) {
                    unset($filteredSettings[$category]);
                } else {
                    foreach ($keys as $key) {
                        unset($filteredSettings[$category][$key]);
                    }
                }
            }
        }

        if (isset($filteredSettings['onboarding']) && is_array($filteredSettings['onboarding'])) {
            foreach ($filteredSettings['onboarding'] as &$step) {
                if (!empty($step['image']) && !Str::startsWith($step['image'], ['http://', 'https://'])) {
                    $step['image'] = url($step['image']);
                }
            }
            unset($step);
        }

        return $filteredSettings;
    }

    public static $wrap = 'values';
}
