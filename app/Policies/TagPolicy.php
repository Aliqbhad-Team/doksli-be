<?php

namespace App\Policies;

use App\Models\Tag;
use App\Models\User;

class TagPolicy
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

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Tag $tag): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Tag $tag): bool
    {
        // Hanya Admin & Pengelola yang boleh mengubah tag
        return $this->isAdmin($user) || $this->isPengelola($user);
    }

    public function delete(User $user, Tag $tag): bool
    {
        // Hanya Admin & Pengelola yang boleh menghapus tag
        return $this->isAdmin($user) || $this->isPengelola($user);
    }
}
