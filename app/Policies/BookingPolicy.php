<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Booking;
use App\Helpers\Helpers;
use Illuminate\Auth\Access\HandlesAuthorization;


class BookingPolicy
{
    use HandlesAuthorization;
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user)
    {
        if ($user->can('backend.booking.index')) {
            return true;
        }
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Booking $booking)
    {
        if ($user->can('backend.booking.index')) {
            return true;
        }
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user)
    {
        if (Helpers::isUserLogin()) {
            if ($user->can('backend.booking.create')) {
                return true;
            }

            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Booking $booking)
    {
        if ($user->can('backend.booking.edit')) {
            return true;
        }
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Booking $booking)
    {
        //
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Booking $booking)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Booking $booking)
    {
        //
    }
}
