<?php

namespace App\Policies;

use App\Models\User;

class ServicemanPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user)
    {
        if ($user->can('backend.serviceman.index')) {
            return true;
        }
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $serviceman)
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user)
    {
        if ($user->can('backend.serviceman.create')) {
            return true;
        }
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user)
    {
        $serviceman = request()->serviceman;
        if ($user->can('backend.serviceman.edit') && $user->id == $serviceman->created_by) {
            return true;
        }
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user)
    {
        $serviceman = request()->serviceman;
        if ($user->can('backend.serviceman.destroy') && $user->id == $serviceman->created_by) {
            return true;
        }
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $serviceman)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $serviceman)
    {
        //
    }
}
