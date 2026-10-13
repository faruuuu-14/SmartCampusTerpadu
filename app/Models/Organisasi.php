<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

// ==========================================
// PEMBAHASAN / BAGIAN: Model Entitas ORGANISASI
// ==========================================
class Organisasi extends Model
{
    protected $table = 'ORGANISASI';

    protected $primaryKey = 'IDORGANISASI';

    const CREATED_AT = 'TANGGALBUAT';

    const UPDATED_AT = 'TANGGALUBAH';

    protected $fillable = [
        'NAMAORGANISASI',
        'JENISORGANISASI',
        'IDKETUA',
        'IDPEMBINA',
        'PERIODE',
        'STATUSORGANISASI',
    ];

    protected function casts(): array
    {
        return [
            'TANGGALBUAT' => 'datetime',
            'TANGGALUBAH' => 'datetime',
        ];
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Relasi ke Mahasiswa Ketua
    // ==========================================
    public function ketua(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class, 'IDKETUA', 'IDMAHASISWA');
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Relasi ke Dosen Pembina
    // ==========================================
    public function pembina(): BelongsTo
    {
        return $this->belongsTo(Dosen::class, 'IDPEMBINA', 'IDDOSEN');
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Relasi ke Surat ADMINISTRASI
    // ==========================================
    public function administrasi(): HasMany
    {
        return $this->hasMany(Administrasi::class, 'IDORGANISASI', 'IDORGANISASI');
    }
}
