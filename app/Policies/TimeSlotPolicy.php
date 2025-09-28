<?php

namespace App\Policies;

use App\Models\User;
use App\Enums\RoleEnum;
use App\Models\TimeSlot;
use Illuminate\Auth\Access\HandlesAuthorization;

class TimeSlotPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */

    public function viewAny(User $user)
    {
        if ($user->can('backend.provider_time_slot.index')) {
            return true;
        }
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\TimeSlot  $provider_time_slot
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, TimeSlot $provider_time_slot_time_slot)
    {
        if ($user->can('backend.provider_time_slot.index')) {
            return true;
        }
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        if ($user->can('backend.provider_time_slot.create')) {
            return true;
        }
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\TimeSlot  $provider_time_slot
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, TimeSlot $provider_time_slot)
    {
        if ($user->can('backend.provider_time_slot.edit')) {
            return true;
        }
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\TimeSlot  $provider_time_slot
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, TimeSlot $provider_time_slot)
    {
        if ($user->can('backend.provider_time_slot.destroy')) {
            return true;
        }
    }
}
