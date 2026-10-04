<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;

class RolePolicy
{
    // Placeholder: any authenticated user may modify a role until real permissions exist.
    public function update(User $user, Role $role): bool
    {
        return true;
    }

    public function delete(User $user, Role $role): bool
    {
        return true;
    }
}
