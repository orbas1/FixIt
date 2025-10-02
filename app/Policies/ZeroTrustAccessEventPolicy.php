<?php

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Models\User;
use App\Models\ZeroTrustAccessEvent;

class ZeroTrustAccessEventPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(RoleEnum::ADMIN)
            || $user->can('backend.security.zero_trust.view');
    }

    public function view(User $user, ZeroTrustAccessEvent $event): bool
    {
        return $user->id === $event->user_id || $this->viewAny($user);
    }
}
