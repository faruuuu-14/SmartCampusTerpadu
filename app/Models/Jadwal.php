<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

// ==========================================
// PEMBAHASAN / BAGIAN: Model Entitas JADWAL
// ==========================================
class Jadwal extends Model
{
    protected $table = 'JADWAL';

    protected $primaryKey = 'IDJADWAL';

    const CREATED_AT = 'TANGGALBUAT';

    const UPDATED_AT = 'TANGGALUBAH';

    protected $fillable = [
        'IDDOSEN',
        'KODEMATAKULIAH',
        'NAMAMATAKULIAH',
        'SKS',
        'KELAS',
        'HARI',
        'JAMMULAI',
        'JAMSELESAI',
        'RUANGAN',
        'LINTANG',
        'BUJUR',
        'RADIUSMETER',
        'MODEKEHADIRAN',
        'MENITPENGINGAT',
    ];

    protected function casts(): array
    {
        return [
            'SKS' => 'integer',
            'RADIUSMETER' => 'integer',
            'MENITPENGINGAT' => 'integer',
            'LINTANG' => 'float',
            'BUJUR' => 'float',
            'TANGGALBUAT' => 'datetime',
            'TANGGALUBAH' => 'datetime',
        ];
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Relasi ke Pengampu DOSEN
    // ==========================================
    public function dosen(): BelongsTo
    {
        return $this->belongsTo(Dosen::class, 'IDDOSEN', 'IDDOSEN');
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Relasi ke Rencana Studi (KRS)
    // ==========================================
    public function krs(): HasMany
    {
        return $this->hasMany(Krs::class, 'IDJADWAL', 'IDJADWAL');
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Relasi ke Sesi PERTEMUAN Kuliah
    // ==========================================
    public function pertemuan(): HasMany
    {
        return $this->hasMany(Pertemuan::class, 'IDJADWAL', 'IDJADWAL');
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Relasi ke Daftar TUGAS Kuliah
    // ==========================================
    public function tugas(): HasMany
    {
        return $this->hasMany(Tugas::class, 'IDJADWAL', 'IDJADWAL');
    }
}
