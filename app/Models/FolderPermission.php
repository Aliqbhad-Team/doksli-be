<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class FolderPermission extends Model
{
    use HasFactory, HasUuids; // menambahkan HasUuids agar ID otomatis terisi UUID saat create data

    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    // 'id' dihilangkan dari fillable karena sudah diurus otomatis oleh trait HasUuids
    protected $fillable = [
        'folder_id',
        'subject_type',
        'subject_id',
        'level',
        'granted_by',
        'granted_at',
    ];

    // Format tanggal untuk granted_at
    protected $casts = [
        'granted_at' => 'datetime',
    ];

    // BAGIAN RELASI ANTAR TABEL (ELOQUENT)

    // Relasi balik ke tabel folders

    public function folder(): BelongsTo
    {
        return $this->belongsTo(Folder::class);
    }

    /**
     * Relasi Polymorphic ke users atau groups
     * Ini otomatis mencari model berdasarkan isi kolom 'subject_type'
     */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Relasi ke tabel users untuk mengetahui siapa yang memberi akses
     */
    public function granter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'granted_by');
    }
}