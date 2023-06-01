<?php

namespace App\Policies;
use App\Models\User;

class UserPolicy
{
    public function view(User $user)
    {
        if ($user->role === 'admin') {
            return true;
        }
        return false;
    }
    /**
     * Determine whether the user can view any models.
     */
    public function userAccess(User $user): bool
    {
        return false;
    }
}
