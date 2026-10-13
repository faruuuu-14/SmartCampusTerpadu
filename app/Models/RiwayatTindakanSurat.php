<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// ==========================================
// PEMBAHASAN / BAGIAN: Model Jejak Audit Tindakan Surat
// ==========================================
class RiwayatTindakanSurat extends Model
{
    protected $table = 'RIWAYATTINDAKANSURAT';

    protected $primaryKey = 'IDRIWAYAT';

    public $timestamps = false;

    protected $fillable = [
        'IDADMINISTRASI',
        'IDPELAKU',
        'TINDAKAN',
        'CATATAN',
        'WAKTU',
    ];

    protected function casts(): array
    {
        return ['WAKTU' => 'datetime'];
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Relasi ke Surat dan Pelaku Tindakan
    // ==========================================
    public function administrasi(): BelongsTo
    {
        return $this->belongsTo(Administrasi::class, 'IDADMINISTRASI', 'IDADMINISTRASI');
    }

    public function pelaku(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'IDPELAKU', 'IDPENGGUNA');
    }
}
