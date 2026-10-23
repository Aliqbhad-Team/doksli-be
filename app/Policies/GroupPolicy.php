<?php

namespace App\Policies;

use App\Models\Group;
use App\Models\User;

class GroupPolicy
{
    public function viewAny(?User $user): bool { return true; }
    public function view(?User $user, Group $group): bool { return true; }

    public function create(User $user): bool
    {
        $user->loadMissing('role');
        return strtolower((string) ($user->role?->name ?? '')) === 'admin';
    }

    public function update(User $user, Group $group): bool
    {
        $user->loadMissing('role');
        return strtolower((string) ($user->role?->name ?? '')) === 'admin';
    }

    public function delete(User $user, Group $group): bool
    {
        $user->loadMissing('role');
        return strtolower((string) ($user->role?->name ?? '')) === 'admin';
    }
}