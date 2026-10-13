<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FolderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'name'             => $this->name,
            'parent_folder_id' => $this->parent_folder_id,
            'unit_id'          => $this->unit_id,
            'created_by'       => $this->created_by,
            'created_at'       => $this->created_at,
        ];
    }
}