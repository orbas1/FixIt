<?php

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Models\Secret;
use App\Models\User;

class SecretPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(RoleEnum::ADMIN);
    }

    public function view(User $user, Secret $secret): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function update(User $user, Secret $secret): bool
    {
        return $this->viewAny($user);
    }

    public function delete(User $user, Secret $secret): bool
    {
        return $this->viewAny($user);
    }
}
