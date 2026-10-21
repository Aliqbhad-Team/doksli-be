<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FolderPermissionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'folder_id'    => $this->folder_id,
            'subject_type' => $this->subject_type,
            'subject_id'   => $this->subject_id,
            'level'        => $this->level,
            'granted_by'   => $this->granted_by,
            'granted_at'   => $this->granted_at,
        ];
    }
}