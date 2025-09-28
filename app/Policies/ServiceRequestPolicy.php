<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ServiceRequest;
use Illuminate\Auth\Access\HandlesAuthorization;

class ServiceRequestPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        if ($user->can('backend.service_request.index')) {
            return true;
        }
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User $user
     * @param  \App\Models\ServiceRequest  $serviceRequest
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, ServiceRequest $serviceRequest)
    {
        if ($user->can('backend.service_request.index')) {
            return true;
        }
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        if ($user->can('backend.service_request.create')) {
            return true;
        }
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User $user
     * @param  \App\Models\ServiceRequest  $serviceRequest
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, ServiceRequest $serviceRequest)
    {   
      
        if ($user->can('backend.service_request.edit')) {
            return true;
        }
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\ServiceRequest $serviceRequest
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, ServiceRequest $serviceRequest)
    {
        if ($user->can('backend.service_request.destroy')) {
            return true;
        }
    }
}
