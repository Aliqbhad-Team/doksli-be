<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Folder extends Model
{
    use HasFactory;

    // Matikan auto-increment karena kita pakai UUID
    public $incrementing = false;
    protected $keyType = 'string';

    // Matikan timestamps bawaan karena tabel ini tidak punya updated_at
    public $timestamps = false;

    // Daftarkan kolom yang boleh diisi
    protected $fillable = [
        'id',
        'name',
        'parent_folder_id',
        'unit_id',
        'created_by',
        'created_at'
    ];
}