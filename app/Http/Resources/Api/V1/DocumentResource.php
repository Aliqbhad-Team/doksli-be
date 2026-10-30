<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Document
 */
class DocumentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'folder_id' => $this->folder_id,
            'category_id' => $this->category_id,
            'owner_id' => $this->owner_id,
            'status' => $this->status,
            'deleted_at' => $this->deleted_at,
            'created_at' => $this->created_at,
            'folder' => FolderResource::make($this->whenLoaded('folder')),
            'category' => CategoryResource::make($this->whenLoaded('category')),
            'owner' => UserResource::make($this->whenLoaded('owner')),
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            'latest_version' => DocumentVersionResource::make($this->whenLoaded('latestVersion')),
            'versions' => DocumentVersionResource::collection($this->whenLoaded('versions')),
        ];
    }
}
