<?php

namespace App\Layanan\TandaTanganElektronik;

use App\Models\PersetujuanSurat;
use JsonException;

// ==========================================
// PEMBAHASAN / BAGIAN: Verifikasi Tanda Tangan dan Keutuhan Surat
// ==========================================
class PemverifikasiTte
{
    // ==========================================
    // PEMBAHASAN / BAGIAN: Menyediakan Kanonisasi Muatan Verifikasi
    // ==========================================
    public function __construct(private PembekuIsiSurat $pembekuIsiSurat) {}

    // ==========================================
    // PEMBAHASAN / BAGIAN: Memeriksa Isi Bertanda Tangan dan Sidik Jari Kunci
    // ==========================================
    public function verifikasi(PersetujuanSurat $persetujuan): bool
    {
        if ($persetujuan->ALGORITMA !== 'ED25519'
            || ! $persetujuan->MUATANTTD
            || ! $persetujuan->TANDATANGAN
            || ! $persetujuan->SIDIKJARIKUNCI) {
            return false;
        }

        $kunci = base64_decode((string) $persetujuan->penyetuju?->KUNCIPUBLIK, true);
        $tandaTangan = base64_decode($persetujuan->TANDATANGAN, true);

        if ($kunci === false || $tandaTangan === false
            || strlen($kunci) !== SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES
            || ! hash_equals($persetujuan->SIDIKJARIKUNCI, hash('sha256', $kunci))) {
            return false;
        }

        $surat = $persetujuan->administrasi;
        $penyetuju = $persetujuan->penyetuju;
        if ($surat === null || $penyetuju === null || $persetujuan->STATUSPERSETUJUAN !== 'DISETUJUI') {
            return false;
        }

        try {
            $muatan = json_decode($persetujuan->MUATANTTD, true, 512, JSON_THROW_ON_ERROR);
            $jsonKanonik = $this->pembekuIsiSurat->jsonKanonik($muatan);
        } catch (JsonException|\TypeError) {
            return false;
        }

        if (! is_array($muatan)
            || ! hash_equals($jsonKanonik, $persetujuan->MUATANTTD)
            || (string) ($muatan['IDADMINISTRASI'] ?? '') !== (string) $surat->IDADMINISTRASI
            || (string) ($muatan['NOMORSURAT'] ?? '') !== (string) $surat->NOMORSURAT
            || (string) ($muatan['HASHISI'] ?? '') !== (string) $surat->HASHISI
            || (string) ($muatan['IDPENYETUJU'] ?? '') !== (string) $persetujuan->IDPENYETUJU
            || (string) ($muatan['URUTAN'] ?? '') !== (string) $persetujuan->URUTAN
            || ! is_string($muatan['WAKTU'] ?? null)
            || $persetujuan->WAKTUKEPUTUSAN === null
            || strtotime($muatan['WAKTU']) !== $persetujuan->WAKTUKEPUTUSAN->timestamp
            || $surat->HASHISI === null
            || ! hash_equals($surat->HASHISI, hash('sha256', (string) $surat->ISISURATKANONIK))) {
            return false;
        }

        return sodium_crypto_sign_verify_detached($tandaTangan, $persetujuan->MUATANTTD, $kunci);
    }
}
