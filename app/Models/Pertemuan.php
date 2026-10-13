<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

// ==========================================
// PEMBAHASAN / BAGIAN: Model Entitas PERTEMUAN
// ==========================================
class Pertemuan extends Model
{
    protected $table = 'PERTEMUAN';

    protected $primaryKey = 'IDPERTEMUAN';

    const CREATED_AT = 'TANGGALBUAT';

    const UPDATED_AT = 'TANGGALUBAH';

    protected $fillable = [
        'IDJADWAL',
        'PERTEMUANKE',
        'TANGGALPERTEMUAN',
        'STATUSPERTEMUAN',
        'MODEKEHADIRAN',
        'BUKAPRESENSI',
        'TUTUPPRESENSI',
        'TOKENSESI',
        'ALASANBATAL',
        'DIBATALKANVIA',
        'JUDULMATERI',
        'FILEMATERI',
    ];

    protected function casts(): array
    {
        return [
            'PERTEMUANKE' => 'integer',
            'TANGGALPERTEMUAN' => 'date',
            'BUKAPRESENSI' => 'datetime',
            'TUTUPPRESENSI' => 'datetime',
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
    // PEMBAHASAN / BAGIAN: Relasi ke Rekap PRESENSI
    // ==========================================
    public function presensi(): HasMany
    {
        return $this->hasMany(Presensi::class, 'IDPERTEMUAN', 'IDPERTEMUAN');
    }
}
