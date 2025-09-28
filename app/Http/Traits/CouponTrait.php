<?php

namespace App\Http\Traits;

use App\Helpers\Helpers;
use Carbon\Carbon;
use Exception;
use Modules\Coupon\Entities\Coupon;

trait CouponTrait
{
    public function updateCouponUsage($coupon_id)
    {
        return Coupon::findOrFail($coupon_id)->decrement('usage_per_coupon');
    }

    public function isValidCoupon($coupon, $amount, $consumer)
    {
        if (Helpers::couponIsEnable()) {
            if ($coupon && $this->isValidSpend($coupon, $amount)) {
                if ($this->isCouponUsable($coupon, $consumer) && $this->isNotExpired($coupon)) {
                    return true;
                }
            }
            $this->removeSessionCoupon();
            throw new Exception(__('errors.coupon_code_min_spend',['couponCode' => $coupon->code, 'minSpend' => $coupon->min_spend]), 422);
        }
        $this->removeSessionCoupon();
        throw new Exception(__('errors.coupon_feature_disabled'), 422);
    }

    public function isValidForZone($zoneId, $coupon)
    {
        $zoneIds = $coupon->zones->pluck('id')->toArray();
        return in_array($zoneId, $zoneIds);
    }
    
    public function isValidForUser($user,$coupon){
        $userIds = $coupon->users->pluck('id')->toArray();
        return in_array($user, $userIds);
    }

    public function isCouponUsable($coupon, $consumer)
    {
        if (! $coupon->is_unlimited) {
            if ($coupon->usage_per_customer) {
                $countUsedPerConsumer = Helpers::getCountUsedPerConsumer($coupon->id, $consumer);
                if ($coupon->usage_per_customer <= $countUsedPerConsumer) {
                    $this->removeSessionCoupon();
                    throw new Exception(__('errors.coupon_max_usage_reached',['couponCode' => $coupon->code, 'usagePerCustomer' => $coupon->usage_per_customer]), 422);
                }
            }

            if ($coupon->usage_per_coupon <= 0) {
                $this->removeSessionCoupon();
                throw new Exception(__('errors.coupon_usage_limit_per_coupon',['couponCode' => $coupon->code, 'usagePerCoupon' => $coupon->usage_per_coupon]), 422);
            }
        }

        return true;
    }

    public function isValidSpend($coupon, $amount)
    {
        return $amount >= $coupon->min_spend;
    }

    public function isNotExpired($coupon)
    {
        if ($coupon->is_expired) {
            if (! $this->isOptimumDate($coupon)) {
                $this->removeSessionCoupon();
                throw new Exception(__('errors.coupon_validity_period',['couponCode' => $coupon->code, 'startDate' => $coupon->start_date, 'endDate' => $coupon->end_date]), 422);
            }
        }
        return true;
    }

    public function isOptimumDate($coupon)
    {
        $currentDate = Carbon::now()->format('Y-m-d');
        if (max(min($currentDate, $coupon->end_date), $coupon->start_date) == $currentDate) {
            return true;
        }
        $this->removeSessionCoupon();
        return false;
    }

    public function isIncludeOrExclude($coupon, $product)
    {
        if ($coupon->is_apply_all) {
            if (isset($coupon->exclude_services)) {
                if (in_array($product['service_id'], array_column($coupon->exclude_services->toArray(), 'id'))) {
                    return false;
                }
            }

            return true;
        }

        if (isset($coupon->services)) {
            if (in_array($product['service_id'], array_column($coupon->services->toArray(), 'id'))) {
                return true;
            }
        }

        $this->removeSessionCoupon();
        return false;
    }

    public function fixedDiscount($subtotal, $couponAmount)
    {
        if ($subtotal >= $couponAmount && $subtotal > 0) {
            return $couponAmount;
        }

        return 0;
    }

    public function percentageDiscount($subtotal, $couponAmount)
    {
        if ($subtotal >= $couponAmount && $subtotal > 0) {
            return ($subtotal * $couponAmount) / 100;
        }

        return 0;
    }

    public function removeSessionCoupon()
    {
        if(session()?->has('coupon')) {
            session()->forget('coupon');
        }
    }
}
