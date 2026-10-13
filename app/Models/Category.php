<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    // 1. Matikan auto-increment karena memakai UUID (string)
    public $incrementing = false;
    protected $keyType = 'string';

    // 2. mematikan timestamps bawaan laravel (created_at & updated_at)
    public $timestamps = false;

    // 3. Daftarkan semua kolom yang boleh diisi
    protected $fillable = [
        'id', 
        'name', 
        'created_by', 
        'created_dt', 
        'updated_by', 
        'updated_dt'
    ];
}