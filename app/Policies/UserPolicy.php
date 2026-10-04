<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    // Placeholder: any authenticated user may modify a user until real permissions exist.
    public function update(User $user, User $model): bool
    {
        return true;
    }

    public function delete(User $user, User $model): bool
    {
        return true;
    }
}
