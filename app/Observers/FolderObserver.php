<?php

namespace App\Observers;

use App\Models\Folder;
use App\Services\AuditService;

class FolderObserver
{
    public function created(Folder $folder): void
    {
        AuditService::log('Buat folder', 'Folder', $folder->id, $folder->name);
    }

    public function updated(Folder $folder): void
    {
        AuditService::log('Perbarui folder', 'Folder', $folder->id, $folder->name);
    }

    public function deleting(Folder $folder): void
    {
        AuditService::log('Hapus folder', 'Folder', $folder->id, $folder->name);
    }
}
