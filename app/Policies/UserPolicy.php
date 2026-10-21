<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    private function roleName(?User $user): string
    {
        if (! $user) return '';
        $user->loadMissing('role');
        return strtolower((string) ($user->role?->name ?? ''));
    }

    private function isAdmin(User $user): bool
    {
        return $this->roleName($user) === 'admin';
    }

    private function isPengelola(User $user): bool
    {
        return $this->roleName($user) === 'pengelola';
    }

    public function viewAny(?User $user): bool { return true; }
    public function view(?User $user, User $model): bool { return true; }

    public function create(User $user): bool
    {
        $role = $this->roleName($user);
        return in_array($role, ['admin', 'pengelola'], true);
    }

    public function update(User $user, User $model): bool
    {
        $actorRole = $this->roleName($user);
        $model->loadMissing('role');
        $targetRole = strtolower((string) ($model->role?->name ?? ''));

        if ($actorRole === 'admin') {
            // admin boleh update Pengelola & Pengguna, tidak boleh update admin lain/self admin
            if ($targetRole === 'admin') return false;
            return true;
        }

        if ($actorRole === 'pengelola') {
            // pengelola hanya boleh update Pengguna di unit yang sama
            if ($targetRole !== 'pengguna') return false;
            // harus satu unit
            if ($user->unit_id && $model->unit_id && (string) $user->unit_id !== (string) $model->unit_id) {
                return false;
            }
            // jika salah satu tidak punya unit, tetap izinkan (fallback)
            return true;
        }

        return false;
    }

    public function delete(User $user, User $model): bool
    {
        $actorRole = $this->roleName($user);
        $model->loadMissing('role');
        $targetRole = strtolower((string) ($model->role?->name ?? ''));

        // tidak boleh hapus diri sendiri & tidak boleh hapus admin
        if ($user->id === $model->id) return false;
        if ($targetRole === 'admin') return false;

        if ($actorRole === 'admin') {
            return in_array($targetRole, ['pengelola', 'pengguna'], true);
        }

        if ($actorRole === 'pengelola') {
            if ($targetRole !== 'pengguna') return false;
            if ($user->unit_id && $model->unit_id && (string) $user->unit_id !== (string) $model->unit_id) {
                return false;
            }
            return true;
        }

        return false;
    }
}
