<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// ==========================================
// PEMBAHASAN / BAGIAN: Model Entitas PRESENSI
// ==========================================
class Presensi extends Model
{
    protected $table = 'PRESENSI';

    protected $primaryKey = 'IDPRESENSI';

    const CREATED_AT = 'TANGGALBUAT';

    const UPDATED_AT = 'TANGGALUBAH';

    protected $fillable = [
        'IDPERTEMUAN',
        'IDMAHASISWA',
        'WAKTUKIRIM',
        'FILEFOTO',
        'LINTANG',
        'BUJUR',
        'AKURASIGPS',
        'JARAKMETER',
        'SKORWAJAH',
        'ALASANWAJAH',
        'SKORLINGKUNGAN',
        'LIVENESSLOLOS',
        'SKORRISIKO',
        'STATUSPRESENSI',
        'IDPENINJAU',
        'SIDIKJARIPERANGKAT',
        'IDPESANWA',
    ];

    protected function casts(): array
    {
        return [
            'WAKTUKIRIM' => 'datetime',
            'LINTANG' => 'float',
            'BUJUR' => 'float',
            'AKURASIGPS' => 'float',
            'JARAKMETER' => 'float',
            'SKORWAJAH' => 'float',
            'SKORLINGKUNGAN' => 'float',
            'LIVENESSLOLOS' => 'boolean',
            'SKORRISIKO' => 'integer',
            'TANGGALBUAT' => 'datetime',
            'TANGGALUBAH' => 'datetime',
        ];
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Relasi ke Sesi PERTEMUAN
    // ==========================================
    public function pertemuan(): BelongsTo
    {
        return $this->belongsTo(Pertemuan::class, 'IDPERTEMUAN', 'IDPERTEMUAN');
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Relasi ke Peserta MAHASISWA
    // ==========================================
    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class, 'IDMAHASISWA', 'IDMAHASISWA');
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Relasi ke Peninjau PENGGUNA (Dosen/Admin)
    // ==========================================
    public function peninjau(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'IDPENINJAU', 'IDPENGGUNA');
    }
}
