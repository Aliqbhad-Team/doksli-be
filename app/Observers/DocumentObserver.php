<?php

namespace App\Observers;

use App\Models\Document;
use App\Services\AuditService;

class DocumentObserver
{
    public function created(Document $document): void
    {
        AuditService::log('Unggah dokumen', 'Document', $document->id, $document->name);
    }

    public function updated(Document $document): void
    {
        if ($document->wasChanged('status')) {
            $statusVal = is_object($document->status) ? $document->status->value : (string) $document->status;
            if ($statusVal === 'trashed') {
                AuditService::log('Pindahkan ke tempat sampah', 'Document', $document->id, $document->name);
                return;
            } elseif ($statusVal === 'active') {
                AuditService::log('Pulihkan dokumen', 'Document', $document->id, $document->name);
                return;
            }
        }

        if ($document->wasChanged('folder_id')) {
            AuditService::log('Pindah dokumen', 'Document', $document->id, $document->name);
            return;
        }

        AuditService::log('Perbarui dokumen', 'Document', $document->id, $document->name);
    }

    public function deleting(Document $document): void
    {
        AuditService::log('Hapus permanen dokumen', 'Document', $document->id, $document->name);
    }
}

