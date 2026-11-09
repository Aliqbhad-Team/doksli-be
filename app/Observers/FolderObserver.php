<?php

namespace App\Observers;

use App\Models\Folder;
use App\Services\AuditService;

class FolderObserver
{
    public function created(Folder $folder): void
    {
        $action = $folder->parent_folder_id ? 'Buat sub-folder' : 'Buat folder';
        AuditService::log($action, 'Folder', $folder->id, $folder->name);
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
