<?php

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Models\BankDetail;
use App\Models\User;

class BankDetailPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user)
    {
        if ($user->can('backend.bank_detail.index')) {
            return true;
        }
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, BankDetail $bankDetail)
    {
        if ($user->can('backend.bank_detail.index')) {
            return true;
        }
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user)
    {
        if ($user->can('backend.bank_detail.create')) {
            return true;
        }
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, BankDetail $bankDetail)
    {
        if ($user->can('backend.bank_detail.edit') && ($user->role->name != RoleEnum::PROVIDER || $user->id == $bankDetail->user_id)) {
            return true;
        }
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, BankDetail $bankDetail)
    {
        if ($user->can('backend.bank_detail.destroy')) {
            return true;
        }
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, BankDetail $bankDetail)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, BankDetail $bankDetail)
    {
        //
    }
}
