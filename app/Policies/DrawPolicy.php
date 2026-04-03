<?php

namespace App\Policies;

use App\Models\Raffle;
use App\Models\User;

class DrawPolicy
{
    /**
     * Determine whether the user can execute the draw.
     */
    public function execute(User $user, Raffle $raffle): bool
    {
        return $user->role === 'admin' && $raffle->status === 'active';
    }
}
