<?php

namespace App\Policies;

use App\Models\DocumentVersion;
use App\Models\User;

class DocumentVersionPolicy
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

    public function view(User $user, DocumentVersion $documentVersion): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, DocumentVersion $documentVersion): bool
    {
        // Admin & Pengelola boleh update semua versi
        if ($this->isAdmin($user) || $this->isPengelola($user)) {
            return true;
        }

        // Pengguna: hanya jika dia yang upload atau pemilik dokumennya
        return $user->id === $documentVersion->uploaded_by
            || $user->id === $documentVersion->document?->owner_id;
    }

    public function delete(User $user, DocumentVersion $documentVersion): bool
    {
        // Admin & Pengelola boleh hapus semua versi
        if ($this->isAdmin($user) || $this->isPengelola($user)) {
            return true;
        }

        // Pengguna: hanya jika dia yang upload atau pemilik dokumennya
        return $user->id === $documentVersion->uploaded_by
            || $user->id === $documentVersion->document?->owner_id;
    }
}
