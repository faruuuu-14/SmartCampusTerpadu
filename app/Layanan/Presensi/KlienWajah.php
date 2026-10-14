<?php

// ==========================================
// PEMBAHASAN / BAGIAN: Klien HTTP Lokal untuk API Wajah
// ==========================================

namespace App\Layanan\Presensi;

use App\Models\Mahasiswa;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use LogicException;
use Throwable;

class KlienWajah
{
    /**
     * @return array<string, mixed>
     *
     * @throws ConnectionException
     * @throws RequestException
     */
    public function verifikasi(UploadedFile $foto, Mahasiswa $mahasiswa): array
    {
        $embedding = $mahasiswa->EMBEDDINGWAJAH;
        $model = $mahasiswa->MODELWAJAH;

        if (! is_string($embedding) || $embedding === '' || ! is_string($model) || $model === '') {
            throw new LogicException('Mahasiswa belum memiliki embedding wajah aktif.');
        }

        return $this->kirimFoto('/v1/verifikasi', $foto, [
            'model' => $model,
            'referensi_base64' => Crypt::decryptString($embedding),
        ]);
    }

    /**
     * @return array<string, mixed>
     *
     * @throws ConnectionException
     * @throws RequestException
     */
    public function buatEmbedding(UploadedFile $foto, string $model): array
    {
        return $this->kirimFoto('/v1/embedding', $foto, ['model' => $model]);
    }

    /**
     * @return array<string, mixed>
     *
     * @throws ConnectionException
     * @throws RequestException
     */
    public function kesehatan(): array
    {
        $url = $this->urlDasar();

        return Http::acceptJson()
            ->withOptions(['allow_redirects' => false])
            ->withHeader('X-KUNCI-LAYANAN', $this->kunci())
            ->connectTimeout(3)
            ->timeout(10)
            ->retry(
                2,
                100,
                fn (Throwable $kesalahan): bool => $kesalahan instanceof ConnectionException
                    || ($kesalahan instanceof RequestException && $kesalahan->response->serverError()),
            )
            ->get($url.'/v1/kesehatan')
            ->throw()
            ->json();
    }

    /**
     * @param  array<string, string>  $isian
     * @return array<string, mixed>
     *
     * @throws ConnectionException
     * @throws RequestException
     */
    private function kirimFoto(string $jalur, UploadedFile $foto, array $isian): array
    {
        $url = $this->urlDasar();

        return Http::acceptJson()
            ->withOptions(['allow_redirects' => false])
            ->withHeader('X-KUNCI-LAYANAN', $this->kunci())
            ->connectTimeout(3)
            ->timeout(10)
            ->retry(
                2,
                100,
                fn (Throwable $kesalahan): bool => $kesalahan instanceof ConnectionException
                    || ($kesalahan instanceof RequestException && $kesalahan->response->serverError()),
            )
            ->attach('foto', $foto->getContent(), 'foto.jpg')
            ->post($url.$jalur, $isian)
            ->throw()
            ->json();
    }

    private function urlDasar(): string
    {
        $url = rtrim((string) config('wajah.url'), '/');

        if ($url === '') {
            throw new LogicException('WAJAH_URL harus menunjuk ke layanan wajah lokal.');
        }

        if (! in_array(parse_url($url, PHP_URL_HOST), ['127.0.0.1', 'localhost', '[::1]'], true)) {
            throw new LogicException('WAJAH_URL dibatasi ke loopback agar foto biometrik tidak dikirim ke host lain.');
        }

        return $url;
    }

    private function kunci(): string
    {
        $kunci = config('wajah.kunci');

        if (! is_string($kunci) || $kunci === '') {
            throw new LogicException('WAJAH_KUNCI belum dikonfigurasi.');
        }

        return $kunci;
    }
}
