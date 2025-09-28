<?php

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Models\ServicePackage;
use App\Models\User;

class ServicePackagePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user)
    {
        if ($user->can('backend.service-package.index')) {
            return true;
        }
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ServicePackage $service_package)
    {
        //
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user)
    {
        if ($user->can('backend.service-package.create')) {
            return true;
        }
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ServicePackage $service_package)
    {
        if ($user->can('backend.service-package.edit') && ($user->role->name != RoleEnum::PROVIDER || $user->id == $service_package->created_by_id || $user->id == $service_package->provider_id)) {
            return true;
        }
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ServicePackage $service_package)
    {
        if ($user->can('backend.service-package.destroy') && $user->id == $service_package->created_by_id) {
            return true;
        }
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ServicePackage $service_package)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ServicePackage $service_package)
    {
        //
    }
}
