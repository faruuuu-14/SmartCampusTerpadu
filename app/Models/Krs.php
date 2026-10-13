<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// ==========================================
// PEMBAHASAN / BAGIAN: Model Entitas KRS
// ==========================================
class Krs extends Model
{
    protected $table = 'KRS';

    protected $primaryKey = 'IDKRS';

    const CREATED_AT = 'TANGGALBUAT';

    const UPDATED_AT = 'TANGGALUBAH';

    protected $fillable = [
        'IDJADWAL',
        'IDMAHASISWA',
    ];

    protected function casts(): array
    {
        return [
            'TANGGALBUAT' => 'datetime',
            'TANGGALUBAH' => 'datetime',
        ];
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Relasi ke JADWAL Mata Kuliah
    // ==========================================
    public function jadwal(): BelongsTo
    {
        return $this->belongsTo(Jadwal::class, 'IDJADWAL', 'IDJADWAL');
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Relasi ke Peserta MAHASISWA
    // ==========================================
    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class, 'IDMAHASISWA', 'IDMAHASISWA');
    }
}
