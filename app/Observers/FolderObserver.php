<?php

namespace App\Observers;

use App\Models\Folder;
use App\Services\AuditService;

class FolderObserver
{
    public function created(Folder $folder): void
    {
        // Otomatis mencatat ke audit_logs setiap kali ada folder baru di database
        AuditService::log('CREATE', 'Folder', $folder->id);
    }

}