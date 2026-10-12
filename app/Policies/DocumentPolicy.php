<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\User;

class DocumentPolicy
{
    // Placeholder: any authenticated user may modify a document until real permissions exist.
    public function update(User $user, Document $document): bool
    {
        return true;
    }

    public function delete(User $user, Document $document): bool
    {
        return true;
    }
}
