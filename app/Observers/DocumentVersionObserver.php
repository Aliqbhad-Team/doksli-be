<?php

namespace App\Observers;

use App\Models\DocumentVersion;
use App\Services\AuditService;

class DocumentVersionObserver
{
    public function created(DocumentVersion $version): void
    {
        // Hanya log jika version_number > 1 (karena versi 1 sudah tercakup saat 'Unggah dokumen')
        if ((int) $version->version_number > 1) {
            $docName = $version->document?->name ?? 'Dokumen';
            AuditService::log('Unggah versi baru', 'DocumentVersion', $version->id, "{$docName} (v{$version->version_number})");
        }
    }

    public function deleting(DocumentVersion $version): void
    {
        $docName = $version->document?->name ?? 'Dokumen';
        AuditService::log('Hapus versi dokumen', 'DocumentVersion', $version->id, "{$docName} (v{$version->version_number})");
    }
}

