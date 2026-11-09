<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuditService
{
    /**
     * Fungsi untuk mencatat aktivitas user
     */
    public static function log(
        string $action,
        ?string $objectType = null,
        ?string $objectId = null,
        ?string $objectName = null,
        ?string $userId = null
    ): void
    {
        try {
            $ipAddress = request()?->ip() ?? '127.0.0.1';
            
            // Prioritaskan guard api (JWT), fallback ke Auth default, request user, atau parameter userId
            $resolvedUserId = $userId
                ?? Auth::guard('api')->id()
                ?? Auth::id()
                ?? request()?->user()?->id;

            // Jika masih null (misal dari console, seeder, background task), fallback ke ID user pertama
            if (! $resolvedUserId) {
                $resolvedUserId = User::value('id');
            }

            if (! $resolvedUserId) {
                return;
            }

            AuditLog::create([
                'id'          => Str::uuid()->toString(),
                'user_id'     => $resolvedUserId,
                'action'      => $action,
                'object_type' => $objectType,
                'object_id'   => $objectId,
                'object_name' => $objectName,
                'ip_address'  => $ipAddress,
                'created_at'  => now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Failed to write audit log: ' . $e->getMessage());
        }
    }
}
