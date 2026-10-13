<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

// ==========================================
// PEMBAHASAN / BAGIAN: Model Entitas TUGAS
// ==========================================
class Tugas extends Model
{
    protected $table = 'TUGAS';

    protected $primaryKey = 'IDTUGAS';

    const CREATED_AT = 'TANGGALBUAT';

    const UPDATED_AT = 'TANGGALUBAH';

    protected $fillable = [
        'IDJADWAL',
        'IDDOSEN',
        'JUDULTUGAS',
        'DESKRIPSI',
        'FILETUGAS',
        'TANGGALTENGGAT',
        'MODEPENGERJAAN',
        'DIBUATVIA',
        'TANGGALSIARAN',
    ];

    protected function casts(): array
    {
        return [
            'TANGGALTENGGAT' => 'datetime',
            'TANGGALSIARAN' => 'datetime',
            'TANGGALBUAT' => 'datetime',
            'TANGGALUBAH' => 'datetime',
        ];
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Relasi ke Induk JADWAL Kuliah
    // ==========================================
    public function jadwal(): BelongsTo
    {
        return $this->belongsTo(Jadwal::class, 'IDJADWAL', 'IDJADWAL');
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Relasi ke Pengampu DOSEN
    // ==========================================
    public function dosen(): BelongsTo
    {
        return $this->belongsTo(Dosen::class, 'IDDOSEN', 'IDDOSEN');
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Relasi ke Hasil PENGUMPULAN
    // ==========================================
    public function pengumpulan(): HasMany
    {
        return $this->hasMany(Pengumpulan::class, 'IDTUGAS', 'IDTUGAS');
    }
}
