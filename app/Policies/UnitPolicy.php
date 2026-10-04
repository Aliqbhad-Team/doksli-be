<?php

namespace App\Policies;

use App\Models\Unit;
use App\Models\User;

class UnitPolicy
{
    // Placeholder: any authenticated user may modify a unit until real permissions exist.
    public function update(User $user, Unit $unit): bool
    {
        return true;
    }

    public function delete(User $user, Unit $unit): bool
    {
        return true;
    }
}
