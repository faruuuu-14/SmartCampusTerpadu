<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// ==========================================
// PEMBAHASAN / BAGIAN: Model Entitas PERSETUJUANSURAT
// ==========================================
class PersetujuanSurat extends Model
{
    protected $table = 'PERSETUJUANSURAT';

    protected $primaryKey = 'IDPERSETUJUAN';

    const CREATED_AT = 'TANGGALBUAT';

    const UPDATED_AT = 'TANGGALUBAH';

    protected $fillable = [
        'IDADMINISTRASI',
        'IDPENYETUJU',
        'PERANPENYETUJU',
        'URUTAN',
        'STATUSPERSETUJUAN',
        'CATATAN',
        'WAKTUKEPUTUSAN',
        'MUATANTTD',
        'TANDATANGAN',
        'ALGORITMA',
        'SIDIKJARIKUNCI',
        'TOKENVERIFIKASI',
    ];

    protected function casts(): array
    {
        return [
            'URUTAN' => 'integer',
            'WAKTUKEPUTUSAN' => 'datetime',
            'TANGGALBUAT' => 'datetime',
            'TANGGALUBAH' => 'datetime',
        ];
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Relasi ke Surat Induk ADMINISTRASI
    // ==========================================
    public function administrasi(): BelongsTo
    {
        return $this->belongsTo(Administrasi::class, 'IDADMINISTRASI', 'IDADMINISTRASI');
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Relasi ke Pejabat PENGGUNA Penyetuju
    // ==========================================
    public function penyetuju(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'IDPENYETUJU', 'IDPENGGUNA');
    }
}
