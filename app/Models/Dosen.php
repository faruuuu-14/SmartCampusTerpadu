<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

// ==========================================
// PEMBAHASAN / BAGIAN: Model Entitas DOSEN
// ==========================================
class Dosen extends Model
{
    protected $table = 'DOSEN';

    protected $primaryKey = 'IDDOSEN';

    const CREATED_AT = 'TANGGALBUAT';

    const UPDATED_AT = 'TANGGALUBAH';

    protected $fillable = [
        'IDPENGGUNA',
        'NIDN',
        'NIP',
        'NAMADOSEN',
        'PROGRAMSTUDI',
        'JABATAN',
        'LEVELPERSETUJUAN',
    ];

    protected function casts(): array
    {
        return [
            'LEVELPERSETUJUAN' => 'integer',
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
    // PEMBAHASAN / BAGIAN: Relasi Mahasiswa Bimbingan PA
    // ==========================================
    public function mahasiswaBimbingan(): HasMany
    {
        return $this->hasMany(Mahasiswa::class, 'IDDOSENPA', 'IDDOSEN');
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Relasi ke Pengampu JADWAL Kuliah
    // ==========================================
    public function jadwal(): HasMany
    {
        return $this->hasMany(Jadwal::class, 'IDDOSEN', 'IDDOSEN');
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Relasi ke TUGAS yang Dibuat
    // ==========================================
    public function tugas(): HasMany
    {
        return $this->hasMany(Tugas::class, 'IDDOSEN', 'IDDOSEN');
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Relasi ke Pembina ORGANISASI
    // ==========================================
    public function organisasiBinaan(): HasMany
    {
        return $this->hasMany(Organisasi::class, 'IDPEMBINA', 'IDDOSEN');
    }
}
