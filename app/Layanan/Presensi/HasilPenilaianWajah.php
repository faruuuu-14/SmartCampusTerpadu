<?php

// ==========================================
// PEMBAHASAN / BAGIAN: Hasil Terstruktur Penilaian Wajah
// ==========================================

namespace App\Layanan\Presensi;

final readonly class HasilPenilaianWajah
{
    public function __construct(
        public ?float $skor,
        public int $jumlahWajah,
        public ?string $model,
        public float $waktuProsesMs,
        public ?string $alasan = null,
    ) {}

    public function perluDitinjau(): bool
    {
        return $this->skor === null || $this->jumlahWajah !== 1;
    }
}
