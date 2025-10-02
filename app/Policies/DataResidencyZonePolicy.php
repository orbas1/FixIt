<?php

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Models\DataResidencyZone;
use App\Models\User;

class DataResidencyZonePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasGovernanceAuthority($user);
    }

    public function view(User $user, DataResidencyZone $zone): bool
    {
        return $this->hasGovernanceAuthority($user);
    }

    public function create(User $user): bool
    {
        return $this->hasGovernanceAuthority($user);
    }

    public function update(User $user, DataResidencyZone $zone): bool
    {
        return $this->hasGovernanceAuthority($user);
    }

    public function delete(User $user, DataResidencyZone $zone): bool
    {
        return $this->hasGovernanceAuthority($user);
    }

    private function hasGovernanceAuthority(User $user): bool
    {
        return $user->hasRole(RoleEnum::ADMIN)
            || $user->can('backend.compliance.data_governance.manage')
            || $user->can('backend.setting.edit');
    }
}
