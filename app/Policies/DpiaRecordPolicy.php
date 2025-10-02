<?php

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Models\DataAsset;
use App\Models\DpiaRecord;
use App\Models\User;

class DpiaRecordPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasGovernanceAuthority($user);
    }

    public function view(User $user, DpiaRecord $record): bool
    {
        return $this->hasGovernanceAuthority($user) || $record->asset?->steward_id === $user->getKey();
    }

    public function create(User $user, ?DataAsset $asset = null): bool
    {
        if ($asset) {
            return $this->hasGovernanceAuthority($user) || $asset->steward_id === $user->getKey();
        }

        return $this->hasGovernanceAuthority($user);
    }

    public function update(User $user, DpiaRecord $record): bool
    {
        return $this->hasGovernanceAuthority($user) || $record->asset?->steward_id === $user->getKey();
    }

    public function delete(User $user, DpiaRecord $record): bool
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
