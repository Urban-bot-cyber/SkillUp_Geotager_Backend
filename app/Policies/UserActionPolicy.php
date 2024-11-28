<?php

namespace App\Policies;

use App\Models\User;

class UserActionPolicy
{
    /**
     * Determine if the user can view actions.
     */
    public function viewActions(User $user)
    {
        return $user->role === 'admin';
    }
}