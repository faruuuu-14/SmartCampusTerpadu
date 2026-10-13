<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

// ==========================================
// PEMBAHASAN / BAGIAN: Model Entitas ADMINISTRASI
// ==========================================
class Administrasi extends Model
{
    protected $table = 'ADMINISTRASI';

    protected $primaryKey = 'IDADMINISTRASI';

    const CREATED_AT = 'TANGGALBUAT';

    const UPDATED_AT = 'TANGGALUBAH';

    protected $fillable = [
        'IDPEMOHON',
        'IDORGANISASI',
        'JENISSURAT',
        'PERIHAL',
        'KEPERLUAN',
        'DATAFORMULIR',
        'FILELAMPIRAN',
        'STATUSADMINISTRASI',
        'NOMORSURAT',
        'ISISURATKANONIK',
        'HASHISI',
        'HASHBERKAS',
        'FILEAKHIR',
        'TANGGALTERBIT',
        'CATATAN',
    ];

    protected function casts(): array
    {
        return [
            'DATAFORMULIR' => 'array',
            'TANGGALTERBIT' => 'datetime',
            'TANGGALBUAT' => 'datetime',
            'TANGGALUBAH' => 'datetime',
        ];
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Relasi ke Pemohon PENGGUNA
    // ==========================================
    public function pemohon(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'IDPEMOHON', 'IDPENGGUNA');
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Relasi ke Asal ORGANISASI
    // ==========================================
    public function organisasi(): BelongsTo
    {
        return $this->belongsTo(Organisasi::class, 'IDORGANISASI', 'IDORGANISASI');
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Relasi ke Jejak PERSETUJUANSURAT
    // ==========================================
    public function persetujuanSurat(): HasMany
    {
        return $this->hasMany(PersetujuanSurat::class, 'IDADMINISTRASI', 'IDADMINISTRASI');
    }
}
