<?php

namespace Modules\Coupon\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\Coupon\Entities\Coupon;

class CouponPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @return mixed
     */
    public function viewAny(User $user)
    {
        if ($user->can('backend.coupon.index')) {
            return true;
        }
    }

    /**
     * Determine whether the user can view the model.
     *
     * @return mixed
     */
    public function view(User $user, Coupon $coupon)
    {
        if ($user->can('backend.coupon.index')) {
            return true;
        }
    }

    /**
     * Determine whether the user can create models.
     *
     * @return mixed
     */
    public function create(User $user)
    {
        if ($user->can('backend.coupon.create')) {
            return true;
        }
    }

    /**
     * Determine whether the user can update the model.
     *
     * @return mixed
     */
    public function update(User $user, Coupon $coupon)
    {
        if ($user->can('backend.coupon.edit') || $user->id == $coupon->created_by) {
            return true;
        }
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @return mixed
     */
    public function delete(User $user, Coupon $coupon)
    {
        if ($user->can('backend.coupon.destroy') || $user->id == $coupon->created_by) {
            return true;
        }
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @return mixed
     */
    public function restore(User $user, Coupon $coupon)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @return mixed
     */
    public function forceDelete(User $user, Coupon $coupon)
    {
        //
    }
}
