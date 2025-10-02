<?php

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Models\TrustedDevice;
use App\Models\User;

class TrustedDevicePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, TrustedDevice $device): bool
    {
        return $user->id === $device->user_id || $user->hasRole(RoleEnum::ADMIN);
    }

    public function update(User $user, TrustedDevice $device): bool
    {
        return $user->id === $device->user_id || $user->hasRole(RoleEnum::ADMIN);
    }

    public function delete(User $user, TrustedDevice $device): bool
    {
        return $user->id === $device->user_id || $user->hasRole(RoleEnum::ADMIN);
    }
}
