<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Folder extends Model
{
    use HasFactory, HasUuids;

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

    // --- RELASI SELF-JOIN (REVISI PEMBIMBING) ---

    // Relasi ke folder induknya (ke atas)
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Folder::class, 'parent_folder_id');
    }

    // Relasi ke sub-folder langsung di bawahnya (satu tingkat ke bawah)
    public function children(): HasMany
    {
        return $this->hasMany(Folder::class, 'parent_folder_id');
    }

    // Relasi recursive untuk mengambil seluruh anak/sub-folder tanpa batas kedalaman
    public function allChildren(): HasMany
    {
        return $this->children()->with('allChildren');
    }
}