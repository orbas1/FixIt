<?php

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Models\SecurityIncident;
use App\Models\User;

class SecurityIncidentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(RoleEnum::ADMIN);
    }

    public function view(User $user, SecurityIncident $incident): bool
    {
        if ($user->hasRole(RoleEnum::ADMIN)) {
            return true;
        }

        return in_array($user->id, [
            $incident->created_by,
            $incident->acknowledged_by,
            $incident->resolved_by,
        ], true);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(RoleEnum::ADMIN);
    }

    public function update(User $user, SecurityIncident $incident): bool
    {
        return $user->hasRole(RoleEnum::ADMIN);
    }

    public function acknowledge(User $user, SecurityIncident $incident): bool
    {
        return $user->hasRole(RoleEnum::ADMIN) && $incident->status !== 'closed';
    }

    public function resolve(User $user, SecurityIncident $incident): bool
    {
        return $user->hasRole(RoleEnum::ADMIN) && $incident->status !== 'closed';
    }
}
