<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\User;

class DocumentPolicy
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

    public function view(User $user, Document $document): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Document $document): bool
    {
        // Admin & Pengelola boleh update semua dokumen
        if ($this->isAdmin($user) || $this->isPengelola($user)) {
            return true;
        }

        // Pengguna hanya boleh update dokumen miliknya sendiri
        return $user->id === $document->owner_id;
    }

    public function delete(User $user, Document $document): bool
    {
        // Admin & Pengelola boleh hapus semua dokumen
        if ($this->isAdmin($user) || $this->isPengelola($user)) {
            return true;
        }

        // Pengguna hanya boleh hapus dokumen miliknya sendiri
        return $user->id === $document->owner_id;
    }
}
