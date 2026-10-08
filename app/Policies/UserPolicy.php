<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    private function isAdmin(User $user): bool
    {
        $user->loadMissing('role');
        return strtolower((string) ($user->role?->name ?? '')) === 'admin';
    }

    public function viewAny(?User $user): bool { return true; }
    public function view(?User $user, User $model): bool { return true; }

    public function create(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function update(User $user, User $model): bool
    {
        return $this->isAdmin($user);
    }

    public function delete(User $user, User $model): bool
    {
        if (! $this->isAdmin($user)) return false;
        // cegah hapus admin itu sendiri / admin lain -> singleton admin
        $model->loadMissing('role');
        if (strtolower((string) ($model->role?->name ?? '')) === 'admin') return false;
        // cegah admin hapus dirinya sendiri
        if ($user->id === $model->id) return false;
        return true;
    }
}
