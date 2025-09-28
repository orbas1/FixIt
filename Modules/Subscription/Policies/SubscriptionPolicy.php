<?php

namespace Modules\Subscription\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\Subscription\Entities\UserSubscription;
use Modules\Subscription\Enums\RoleEnum;

class SubscriptionPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function viewAny(User $user)
    {
        return false;
    }

    public function purchase(User $user)
    {
        if ($user->hasRole(RoleEnum::PROVIDER)) {
            return true;
        }

        return false;
    }

    public function cancel(User $user, UserSubscription $userSubscription)
    {
        return $user->hasRole(RoleEnum::PROVIDER) && $user->id === $userSubscription->user_id;
    }
}
