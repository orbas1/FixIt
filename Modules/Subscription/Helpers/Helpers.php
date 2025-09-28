<?php

use Modules\Subscription\Entities\UserSubscription;

if (! function_exists('isPlanAllowed')) {
    function isPlanAllowed($field, $maxItems, $user_id = null)
    {
        $userSubscriptions = UserSubscription::where($user_id ?? auth()?->user()?->id);
        if ($userSubscriptions) {
            $allowed_max = $userSubscriptions->where('is_active', true)?->latest()?->value($field);
            if ($allowed_max) {
                if ($allowed_max >= $maxItems) {
                    return true;
                }
            }
        }
    }
}
