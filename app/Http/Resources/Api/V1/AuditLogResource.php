<?php

namespace App\Http\Resources\Api\V1;

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

        return [
            'id'          => $this->id,
            'user_id'     => $this->user_id,
            'user'        => $userName,
            'action'      => $this->action,
            'object_type' => $this->object_type,
            'object_id'   => $this->object_id,
            'object'      => $this->object_type ? "{$this->object_type}" : '—',
            'ip'          => $this->ip_address,
            'time'        => $timeStr,
            'created_at'  => $this->created_at,
        ];
    }
}

