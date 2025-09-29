<?php

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;
use Illuminate\Contracts\Auth\MustVerifyEmail;

class ServiceRequestPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user): Response
    {
        if ($user->can('backend.service_request.index') || $user->hasRole(RoleEnum::ADMIN)) {
            return Response::allow();
        }

        if ($user->hasAnyRole([RoleEnum::PROVIDER, RoleEnum::CONSUMER])) {
            return $this->ensureActiveAccount($user);
        }

        return Response::deny('You are not authorised to access the marketplace feed.');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User $user
     * @param  \App\Models\ServiceRequest  $serviceRequest
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, ServiceRequest $serviceRequest): Response
    {
        if ($user->can('backend.service_request.index') || $user->hasRole(RoleEnum::ADMIN)) {
            return Response::allow();
        }

        if ($user->hasRole(RoleEnum::CONSUMER) && $serviceRequest->user_id === $user->id) {
            return $this->ensureActiveAccount($user);
        }

        if ($user->hasRole(RoleEnum::PROVIDER)) {
            if ($serviceRequest->provider_id && $serviceRequest->provider_id === $user->id) {
                return Response::deny('Providers cannot browse their own assignments in the live feed.');
            }

            return $this->ensureActiveAccount($user);
        }

        return Response::deny('You are not authorised to view this service request.');
    }

    protected function ensureActiveAccount(User $user): Response
    {
        if ((int) $user->status !== 1) {
            return Response::deny('Your account is not active.');
        }

        if ($user instanceof MustVerifyEmail && !$user->hasVerifiedEmail()) {
            return Response::deny('Please verify your email address to access the feed.');
        }

        return Response::allow();
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
