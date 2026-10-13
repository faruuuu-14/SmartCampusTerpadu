<?php

namespace App\Layanan\TandaTanganElektronik;

use App\Models\PersetujuanSurat;
use Illuminate\Support\Facades\Crypt;
use JsonException;
use RuntimeException;

// ==========================================
// PEMBAHASAN / BAGIAN: Tanda Tangan Digital Detached Ed25519
// ==========================================
class PenandaTangan
{
    // ==========================================
    // PEMBAHASAN / BAGIAN: Menandatangani Hash Isi Surat
    // ==========================================
    public function tandatangani(PersetujuanSurat $persetujuan): void
    {
        $surat = $persetujuan->administrasi()->firstOrFail();
        $penyetuju = $persetujuan->penyetuju()->firstOrFail();

        if (! $surat->HASHISI || ! $surat->NOMORSURAT || ! $penyetuju->KUNCIPUBLIK || ! $penyetuju->KUNCIPRIVAT) {
            throw new RuntimeException('Isi surat atau pasangan kunci penyetuju belum lengkap.');
        }

        $publik = base64_decode($penyetuju->KUNCIPUBLIK, true);
        $privatTerenkripsi = Crypt::decryptString($penyetuju->KUNCIPRIVAT);
        $privat = base64_decode($privatTerenkripsi, true);

        try {
            if ($publik === false || $privat === false
                || strlen($publik) !== SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES
                || strlen($privat) !== SODIUM_CRYPTO_SIGN_SECRETKEYBYTES) {
                throw new RuntimeException('Format pasangan kunci penyetuju tidak valid.');
            }

            $waktu = now()->toIso8601String();
            $muatan = [
                'HASHISI' => $surat->HASHISI,
                'IDADMINISTRASI' => $surat->IDADMINISTRASI,
                'IDPENYETUJU' => $penyetuju->IDPENGGUNA,
                'NOMORSURAT' => $surat->NOMORSURAT,
                'URUTAN' => $persetujuan->URUTAN,
                'WAKTU' => $waktu,
            ];

            try {
                $json = json_encode(
                    $muatan,
                    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
                );
            } catch (JsonException $exception) {
                throw new RuntimeException('Muatan tanda tangan tidak dapat dibuat.', previous: $exception);
            }

            $persetujuan->forceFill([
                'MUATANTTD' => $json,
                'TANDATANGAN' => base64_encode(sodium_crypto_sign_detached($json, $privat)),
                'ALGORITMA' => 'ED25519',
                'SIDIKJARIKUNCI' => hash('sha256', $publik),
                'TOKENVERIFIKASI' => (string) str()->uuid(),
                'STATUSPERSETUJUAN' => 'DISETUJUI',
                'WAKTUKEPUTUSAN' => $waktu,
            ])->save();
        } finally {
            if (is_string($privat)) {
                sodium_memzero($privat);
            }

            sodium_memzero($privatTerenkripsi);
        }
    }
}
