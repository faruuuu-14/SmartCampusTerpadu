<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

// ==========================================
// PEMBAHASAN / BAGIAN: Model Entitas MAHASISWA
// ==========================================
class Mahasiswa extends Model
{
    protected $table = 'MAHASISWA';

    protected $primaryKey = 'IDMAHASISWA';

    const CREATED_AT = 'TANGGALBUAT';

    const UPDATED_AT = 'TANGGALUBAH';

    protected $fillable = [
        'IDPENGGUNA',
        'NIM',
        'NAMAMAHASISWA',
        'PROGRAMSTUDI',
        'ANGKATAN',
        'IDDOSENPA',
        'EMBEDDINGWAJAH',
        'FOTOREFERENSI',
        'TANGGALKONSENBIOMETRIK',
    ];

    protected function casts(): array
    {
        return [
            'TANGGALKONSENBIOMETRIK' => 'datetime',
            'TANGGALBUAT' => 'datetime',
            'TANGGALUBAH' => 'datetime',
        ];
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Relasi ke Akun PENGGUNA
    // ==========================================
    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'IDPENGGUNA', 'IDPENGGUNA');
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Relasi ke Dosen Pembimbing Akademik
    // ==========================================
    public function dosenPA(): BelongsTo
    {
        return $this->belongsTo(Dosen::class, 'IDDOSENPA', 'IDDOSEN');
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Relasi ke Rencana Studi (KRS)
    // ==========================================
    public function krs(): HasMany
    {
        return $this->hasMany(Krs::class, 'IDMAHASISWA', 'IDMAHASISWA');
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Relasi ke Riwayat PRESENSI
    // ==========================================
    public function presensi(): HasMany
    {
        return $this->hasMany(Presensi::class, 'IDMAHASISWA', 'IDMAHASISWA');
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Relasi ke PENGUMPULAN Tugas
    // ==========================================
    public function pengumpulan(): HasMany
    {
        return $this->hasMany(Pengumpulan::class, 'IDMAHASISWA', 'IDMAHASISWA');
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Relasi ke ORGANISASI yang Dipimpin
    // ==========================================
    public function organisasiDipimpin(): HasMany
    {
        return $this->hasMany(Organisasi::class, 'IDKETUA', 'IDMAHASISWA');
    }
}
