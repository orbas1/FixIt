<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ServicemanWithdrawRequest;

class ServicemanWithdrawRequestPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user)
    {
        if ($user->can('backend.serviceman_withdraw_request.index')) {
            return true;
        }
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ServicemanWithdrawRequest $servicemanWithdrawRequest)
    {
        if ($user->can('backend.serviceman_withdraw_request.index')) {
            return true;
        }
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user)
    {
        if ($user->can('backend.serviceman_withdraw_request.create')) {
            return true;
        }
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ServicemanWithdrawRequest $servicemanWithdrawRequest)
    {
        if ($user->can('backend.serviceman_withdraw_request.action')) {
            return true;
        }
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ServicemanWithdrawRequest $servicemanWithdrawRequest)
    {
        //
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ServicemanWithdrawRequest $servicemanWithdrawRequest)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ServicemanWithdrawRequest $servicemanWithdrawRequest)
    {
        //
    }
}
