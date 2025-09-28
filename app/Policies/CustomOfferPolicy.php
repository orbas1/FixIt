<?php

namespace App\Policies;

use App\Models\User;
use App\Models\CustomOffer;
use Illuminate\Auth\Access\HandlesAuthorization;

class CustomOfferPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any custom offers.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        if ($user->can('backend.custom_offer.index')) {
            return true;
        }
    }

    /**
     * Determine whether the user can view a specific custom offer.
     *
     * @param  \App\Models\User       $user
     * @param  \App\Models\CustomOffer $customOffer
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, CustomOffer $customOffer)
    {
        if ($user->can('backend.custom_offer.index')) {
            return true;
        }
    }

    /**
     * Determine whether the user can create custom offers.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        if ($user->can('backend.custom_offer.create')) {
            return true;
        }
    }

    /**
     * Determine whether the user can update the custom offer.
     *
     * @param  \App\Models\User       $user
     * @param  \App\Models\CustomOffer $customOffer
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, CustomOffer $customOffer)
    {
        if ($user->can('backend.custom_offer.edit')) {
            return true;
        }
    }

    /**
     * Determine whether the user can delete the custom offer.
     *
     * @param  \App\Models\User       $user
     * @param  \App\Models\CustomOffer $customOffer
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, CustomOffer $customOffer)
    {
        if ($user->can('backend.custom_offer.destroy')) {
            return true;
        }
    }
}
