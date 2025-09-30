<?php

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Models\Escrow;
use App\Models\User;

class EscrowPolicy
{
    public function view(User $user, Escrow $escrow): bool
    {
        return $this->isParticipant($user, $escrow) || $user->hasRole(RoleEnum::ADMIN);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole([RoleEnum::CONSUMER, RoleEnum::ADMIN]);
    }

    public function fund(User $user, Escrow $escrow): bool
    {
        return $user->getKey() === $escrow->consumer_id || $user->hasRole(RoleEnum::ADMIN);
    }

    public function release(User $user, Escrow $escrow): bool
    {
        return $user->getKey() === $escrow->consumer_id || $user->hasRole(RoleEnum::ADMIN);
    }

    public function refund(User $user, Escrow $escrow): bool
    {
        return $user->getKey() === $escrow->consumer_id || $user->hasRole(RoleEnum::ADMIN);
    }

    protected function isParticipant(User $user, Escrow $escrow): bool
    {
        return in_array($user->getKey(), [$escrow->consumer_id, $escrow->provider_id], true);
    }
}
