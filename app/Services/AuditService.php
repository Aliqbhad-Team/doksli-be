<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth; // Tambahan import untuk Auth

class AuditService
{
    /**
     * Fungsi untuk mencatat aktivitas user
     */
    public static function log(string $action, string $objectType, string $objectId)
    {
        // Mendapatkan IP Address dari user yang melakukan request
        $ipAddress = request()->ip();
        
        // Mendapatkan user yang sedang login menggunakan Facade Auth
        $userId = Auth::id(); 

        AuditLog::create([
            'id'          => Str::uuid()->toString(),
            'user_id'     => $userId,
            'action'      => $action,       // Contoh: 'CREATE', 'UPDATE', 'DELETE'
            'object_type' => $objectType,   // Contoh: 'Folder', 'FolderPermission'
            'object_id'   => $objectId,     // UUID dari data yang diproses
            'ip_address'  => $ipAddress,
            'created_at'  => now()
        ]);
    }
}