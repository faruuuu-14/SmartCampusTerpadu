<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// ==========================================
// PEMBAHASAN / BAGIAN: Model Entitas PENGUMPULAN
// ==========================================
class Pengumpulan extends Model
{
    protected $table = 'PENGUMPULAN';

    protected $primaryKey = 'IDPENGUMPULAN';

    const CREATED_AT = 'TANGGALBUAT';

    const UPDATED_AT = 'TANGGALUBAH';

    protected $fillable = [
        'IDTUGAS',
        'IDMAHASISWA',
        'STATUSPENGUMPULAN',
        'FILEKUMPUL',
        'HASHFILE',
        'WAKTUKUMPUL',
        'FOTOVERIFIKASI',
        'SKORWAJAH',
        'NILAI',
        'UMPANBALIK',
    ];

    protected function casts(): array
    {
        return [
            'WAKTUKIRIM' => 'datetime',
            'WAKTUKUMPUL' => 'datetime',
            'SKORWAJAH' => 'float',
            'NILAI' => 'float',
            'TANGGALBUAT' => 'datetime',
            'TANGGALUBAH' => 'datetime',
        ];
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Relasi ke Induk TUGAS
    // ==========================================
    public function tugas(): BelongsTo
    {
        return $this->belongsTo(Tugas::class, 'IDTUGAS', 'IDTUGAS');
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Relasi ke Peserta MAHASISWA
    // ==========================================
    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class, 'IDMAHASISWA', 'IDMAHASISWA');
    }
}
