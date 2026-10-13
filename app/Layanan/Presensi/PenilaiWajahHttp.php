<?php

namespace App\Layanan\Presensi;

use App\Models\Mahasiswa;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use LogicException;
use UnexpectedValueException;

// ==========================================
// PEMBAHASAN / BAGIAN: Integrasi Penilai Wajah Eksternal
// ==========================================
class PenilaiWajahHttp implements PenilaiWajah
{
    /**
     * @throws ConnectionException
     */
    public function nilai(UploadedFile $foto, Mahasiswa $mahasiswa): float
    {
        $url = config('presensi.penilai_wajah.url');

        if (! is_string($url) || $url === '') {
            throw new LogicException('PENILAI_WAJAH_URL wajib diisi untuk mode http.');
        }

        $permintaan = Http::acceptJson()
            ->connectTimeout(3)
            ->timeout((int) config('presensi.penilai_wajah.timeout_detik'));
        $token = config('presensi.penilai_wajah.token');

        if (is_string($token) && $token !== '') {
            $permintaan->withToken($token);
        }

        $respons = $permintaan
            ->attach('foto', $foto->getContent(), $foto->getClientOriginalName())
            ->post($url, ['IDMAHASISWA' => $mahasiswa->IDMAHASISWA])
            ->throw();
        $skor = $respons->json('SKORWAJAH');

        if (! is_numeric($skor) || (float) $skor < 0 || (float) $skor > 1) {
            throw new UnexpectedValueException('Layanan penilai wajah harus mengembalikan SKORWAJAH antara 0 dan 1.');
        }

        return (float) $skor;
    }
}
