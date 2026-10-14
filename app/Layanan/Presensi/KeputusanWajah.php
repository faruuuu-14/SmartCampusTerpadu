<?php

// ==========================================
// PEMBAHASAN / BAGIAN: Jalur Review dan Ambang Keputusan Wajah
// ==========================================

namespace App\Layanan\Presensi;

use InvalidArgumentException;

class KeputusanWajah
{
    public function statusAwal(
        HasilPenilaianWajah $hasil,
        ?float $ambang,
        string $aksiDiBawahAmbang,
    ): ?string {
        if ($hasil->perluDitinjau() || $ambang === null) {
            return 'PERLUREVIEW';
        }

        if ($ambang < -1 || $ambang > 1) {
            throw new InvalidArgumentException('WAJAHAMBANG harus berada di antara -1 dan 1.');
        }

        if (! in_array($aksiDiBawahAmbang, ['review', 'tolak'], true)) {
            throw new InvalidArgumentException('WAJAH_SKOR_TIDAK_COCOK harus bernilai review atau tolak.');
        }

        if ($hasil->skor < $ambang) {
            return $aksiDiBawahAmbang === 'tolak' ? 'DITOLAK' : 'PERLUREVIEW';
        }

        return null;
    }
}
