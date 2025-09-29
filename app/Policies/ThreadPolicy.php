<?php

namespace App\Policies;

use App\Models\Thread;
use App\Models\ThreadParticipant;
use App\Models\User;

class ThreadPolicy
{
    public function view(User $user, Thread $thread): bool
    {
        return $this->isParticipant($user, $thread);
    }

    public function sendMessage(User $user, Thread $thread): bool
    {
        if (! $this->isParticipant($user, $thread)) {
            return false;
        }

        /** @var ThreadParticipant|null $participant */
        $participant = $thread->participants
            ->firstWhere('user_id', $user->id);

        return $participant?->is_active && ! $participant->isMuted();
    }

    private function isParticipant(User $user, Thread $thread): bool
    {
        if ($thread->relationLoaded('participants')) {
            return $thread->participants->contains('user_id', $user->id);
        }

        return $thread->participants()->where('user_id', $user->id)->exists();
    }
}
