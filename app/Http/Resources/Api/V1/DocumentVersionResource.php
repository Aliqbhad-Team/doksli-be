<?php

namespace App\Http\Resources\Api\V1;

use App\Models\DocumentVersion;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin DocumentVersion
 */
class DocumentVersionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'document_id' => $this->document_id,
            'version_number' => $this->version_number,
            'file_url' => $this->file_url,
            'checksum' => $this->checksum,
            'size_bytes' => $this->size_bytes,
            'note' => $this->note,
            'uploaded_by' => $this->uploaded_by,
            'uploaded_at' => $this->uploaded_at,
            'document' => DocumentResource::make($this->whenLoaded('document')),
            'uploader' => UserResource::make($this->whenLoaded('uploader')),
        ];
    }
}
