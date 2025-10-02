<?php

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Models\NetworkZone;
use App\Models\User;

class NetworkZonePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasSecurityAuthority($user);
    }

    public function view(User $user, NetworkZone $zone): bool
    {
        return $this->hasSecurityAuthority($user);
    }

    public function create(User $user): bool
    {
        return $this->hasSecurityAuthority($user);
    }

    public function update(User $user, NetworkZone $zone): bool
    {
        return $this->hasSecurityAuthority($user);
    }

    public function delete(User $user, NetworkZone $zone): bool
    {
        return $this->hasSecurityAuthority($user);
    }

    protected function hasSecurityAuthority(User $user): bool
    {
        return $user->hasRole(RoleEnum::ADMIN)
            || $user->can('backend.security.zero_trust.manage')
            || $user->can('backend.setting.edit');
    }
}
