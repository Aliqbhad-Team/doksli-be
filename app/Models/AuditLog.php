<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    // Mematikan auto-increment karena ID pakai UUID
    public $incrementing = false;
    protected $keyType = 'string';
    
    // Mematikan timestamps bawaan Laravel cuma butuh created_at
    public $timestamps = false;

    // Mengizinkan kolom diisi secara massal
    protected $fillable = [
        'id', 
        'user_id', 
        'action', 
        'object_type', 
        'object_id', 
        'ip_address', 
        'created_at'
    ];
}