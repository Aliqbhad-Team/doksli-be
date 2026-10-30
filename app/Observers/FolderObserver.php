<?php

namespace App\Observers;

use App\Models\Folder;
use App\Services\AuditService;

class FolderObserver
{
    public function created(Folder $folder): void
    {
        AuditService::log('CREATE', 'Folder', $folder->id);
    }

    public function updated(Folder $folder): void
    {
        AuditService::log('UPDATE', 'Folder', $folder->id);
    }

    public function deleted(Folder $folder): void
    {
        AuditService::log('DELETE', 'Folder', $folder->id);
    }
}