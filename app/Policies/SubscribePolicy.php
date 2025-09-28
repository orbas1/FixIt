<?php

namespace App\Policies;

use App\Models\Subscribe;
use App\Models\User;

class SubscribePolicy
{
    public function view(User $user, Subscribe $subscribe)
    {
        if ($user->can('backend.subscription.index')) {
            return true;
        }
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user)
    {
        if ($user->can('backend.subscription.create')) {
            return true;
        }
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Subscribe $subscribe)
    {
        if ($user->can('backend.subscription.edit')) {
            return true;
        }
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user,  Subscribe $subscribe)
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user,  Subscribe $subscribe)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user,  Subscribe $subscribe)
    {
        //
    }
}