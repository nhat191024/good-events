<?php

namespace App\Policies;

use App\Models\Call;
use App\Models\User;
use Cmgmyr\Messenger\Models\Participant;

class CallPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function view(User $user, Call $call): bool
    {
        return $this->isThreadParticipant($user, $call);
    }

    public function join(User $user, Call $call): bool
    {
        return $call->isActive() && $this->isThreadParticipant($user, $call);
    }

    public function end(User $user, Call $call): bool
    {
        return $call->isActive() && (int) $call->initiated_by === (int) $user->id;
    }

    private function isThreadParticipant(User $user, Call $call): bool
    {
        return Participant::query()
            ->where('thread_id', $call->thread_id)
            ->where('user_id', $user->id)
            ->whereNull('deleted_at')
            ->exists();
    }
}
