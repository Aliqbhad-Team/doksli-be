<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TagResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'created_by' => $this->created_by,
            'created_dt' => $this->created_dt,
            'updated_by' => $this->updated_by,
            'updated_dt' => $this->updated_dt,
        ];
    }
}
