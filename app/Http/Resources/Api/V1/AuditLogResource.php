<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Document;
use App\Models\DocumentVersion;
use App\Models\Folder;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuditLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $userName = $this->user?->name ?? 'Admin';
        $timeStr = $this->created_at 
            ? Carbon::parse($this->created_at)->locale('id')->isoFormat('D MMM Y, HH:mm') 
            : '—';

        // 1. Prioritas gunakan object_name yang tersimpan permanen
        $objectName = $this->object_name;

        // 2. Jika belum ada (misal log lama), cari di database
        if (! $objectName && $this->object_id) {
            $type = strtolower((string) $this->object_type);
            if ($type === 'folder') {
                $objectName = Folder::find($this->object_id)?->name;
            } elseif ($type === 'document' || $type === 'dokumen') {
                $doc = Document::find($this->object_id);
                if ($doc) {
                    $objectName = $doc->name;
                }
            } elseif ($type === 'documentversion' || $type === 'document_version') {
                $version = DocumentVersion::with('document')->find($this->object_id);
                if ($version) {
                    $docName = $version->document?->name ?? 'Dokumen';
                    $objectName = "{$docName} (v{$version->version_number})";
                }
            }
        }

        // 3. Fallback terakhir jika benar-benar tidak ditemukan
        if (! $objectName) {
            $objectName = $this->object_type ?: '—';
        }

        return [
            'id'          => $this->id,
            'user_id'     => $this->user_id,
            'user'        => $userName,
            'action'      => $this->action,
            'object_type' => $this->object_type,
            'object_id'   => $this->object_id,
            'object_name' => $objectName,
            'object'      => $objectName,
            'ip'          => $this->ip_address,
            'time'        => $timeStr,
            'created_at'  => $this->created_at,
        ];
    }
}


