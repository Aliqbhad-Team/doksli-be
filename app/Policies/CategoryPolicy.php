<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;

class CategoryPolicy
{
    public function viewAny(?User $user): bool { return true; }
    public function view(?User $user, Category $category): bool { return true; }

    public function create(User $user): bool
    {
        $user->loadMissing('role');
        return strtolower((string) ($user->role?->name ?? '')) === 'admin';
    }

    public function update(User $user, Category $category): bool
    {
        $user->loadMissing('role');
        return strtolower((string) ($user->role?->name ?? '')) === 'admin';
    }

    public function delete(User $user, Category $category): bool
    {
        $user->loadMissing('role');
        return strtolower((string) ($user->role?->name ?? '')) === 'admin';
    }
}