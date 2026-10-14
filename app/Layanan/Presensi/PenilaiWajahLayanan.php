<?php

// ==========================================
// PEMBAHASAN / BAGIAN: Penilai Wajah melalui Layanan FastAPI Lokal
// ==========================================

namespace App\Layanan\Presensi;

use App\Models\Mahasiswa;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use LogicException;
use UnexpectedValueException;

class PenilaiWajahLayanan implements PenilaiWajahTerperinci
{
    public function __construct(private readonly KlienWajah $klien) {}

    public function nilai(UploadedFile $foto, Mahasiswa $mahasiswa): float
    {
        return $this->nilaiTerperinci($foto, $mahasiswa)->skor ?? 0.0;
    }

    public function nilaiTerperinci(UploadedFile $foto, Mahasiswa $mahasiswa): HasilPenilaianWajah
    {
        if (
            $mahasiswa->STATUSWAJAH !== 'AKTIF'
            || $mahasiswa->TANGGALKONSENBIOMETRIK === null
            || ! is_string($mahasiswa->EMBEDDINGWAJAH)
            || ! is_string($mahasiswa->MODELWAJAH)
        ) {
            return new HasilPenilaianWajah(null, 0, $mahasiswa->MODELWAJAH, 0.0, 'pendaftaran_belum_aktif');
        }

        try {
            $respons = $this->klien->verifikasi($foto, $mahasiswa);
        } catch (ConnectionException|RequestException|LogicException|DecryptException $kesalahan) {
            Log::warning('Penilaian wajah gagal; presensi diarahkan ke peninjauan manual.', [
                'alasan' => $kesalahan::class,
            ]);

            return new HasilPenilaianWajah(null, 0, $mahasiswa->MODELWAJAH, 0.0, 'layanan_tidak_tersedia');
        }

        $jumlahWajah = $respons['face_count'] ?? null;
        $skor = $respons['score'] ?? null;
        $model = $respons['model'] ?? null;
        $waktu = $respons['processing_ms'] ?? 0;

        if (
            ! is_bool($respons['face_found'] ?? null)
            || $respons['face_found'] !== ($jumlahWajah === 1)
            || ! is_int($jumlahWajah)
            || $jumlahWajah < 0
            || ! is_numeric($waktu)
            || ! is_finite((float) $waktu)
            || (float) $waktu < 0
            || ($skor === null && $jumlahWajah === 1)
            || ($skor !== null && (! is_numeric($skor) || ! is_finite((float) $skor) || (float) $skor < -1 || (float) $skor > 1))
            || ! is_string($model)
            || $model !== $mahasiswa->MODELWAJAH
        ) {
            Log::warning('Respons layanan wajah tidak sesuai kontrak; presensi diarahkan ke peninjauan manual.', [
                'alasan' => UnexpectedValueException::class,
            ]);

            return new HasilPenilaianWajah(null, 0, $mahasiswa->MODELWAJAH, 0.0, 'respons_layanan_tidak_valid');
        }

        if ($jumlahWajah === 1 && ! is_numeric($skor)) {
            Log::warning('Respons model atau skor tidak konsisten; presensi diarahkan ke peninjauan manual.', [
                'alasan' => 'model_atau_skor_tidak_konsisten',
            ]);

            return new HasilPenilaianWajah(null, 0, $mahasiswa->MODELWAJAH, 0.0, 'model_atau_skor_tidak_konsisten');
        }

        $skor = $jumlahWajah === 1 && is_numeric($skor) ? (float) $skor : null;
        $alasan = $jumlahWajah === 0
            ? 'wajah_tidak_ditemukan'
            : ($jumlahWajah > 1 ? 'lebih_dari_satu_wajah' : null);

        return new HasilPenilaianWajah(
            $skor,
            $jumlahWajah,
            $model,
            (float) $waktu,
            $alasan,
        );
    }
}
