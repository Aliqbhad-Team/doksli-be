<?php

namespace App\Policies;

use App\Models\DocumentVersion;
use App\Models\User;

class DocumentVersionPolicy
{
    // Placeholder: any authenticated user may modify a document version until real permissions exist.
    public function update(User $user, DocumentVersion $documentVersion): bool
    {
        return true;
    }

    public function delete(User $user, DocumentVersion $documentVersion): bool
    {
        return true;
    }
}
