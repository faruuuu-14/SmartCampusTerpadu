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
        $hasil = $this->periksa($persetujuan);

        return $hasil['valid'];
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Menghasilkan Pemeriksaan Terpisah dan Alasan Kegagalan
    // ==========================================
    /**
     * @return array{tanda_tangan: bool, sidik_jari: bool, hash_isi: bool, muatan: bool, alasan: array<int, string>, valid: bool}
     */
    public function periksa(PersetujuanSurat $persetujuan): array
    {
        $hasil = [
            'tanda_tangan' => false,
            'sidik_jari' => false,
            'hash_isi' => false,
            'muatan' => false,
            'alasan' => [],
            'valid' => false,
        ];

        if ($persetujuan->ALGORITMA !== 'ED25519'
            || ! $persetujuan->MUATANTTD
            || ! $persetujuan->TANDATANGAN
            || ! $persetujuan->SIDIKJARIKUNCI) {
            $hasil['alasan'][] = 'Data tanda tangan tidak lengkap atau algoritma bukan ED25519.';

            return $hasil;
        }

        $kunci = base64_decode((string) $persetujuan->penyetuju?->KUNCIPUBLIK, true);
        $tandaTangan = base64_decode($persetujuan->TANDATANGAN, true);

        if ($kunci === false || strlen($kunci) !== SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES) {
            $hasil['alasan'][] = 'Kunci publik penyetuju tidak valid.';
        } else {
            $hasil['sidik_jari'] = hash_equals($persetujuan->SIDIKJARIKUNCI, hash('sha256', $kunci));
            if (! $hasil['sidik_jari']) {
                $hasil['alasan'][] = 'Sidik jari kunci publik tidak cocok dengan rekaman tanda tangan.';
            }

            if ($tandaTangan !== false && strlen($tandaTangan) === SODIUM_CRYPTO_SIGN_BYTES) {
                $hasil['tanda_tangan'] = sodium_crypto_sign_verify_detached(
                    $tandaTangan,
                    $persetujuan->MUATANTTD,
                    $kunci
                );
            }
        }

        if (! $hasil['tanda_tangan']) {
            $hasil['alasan'][] = 'Tanda tangan detached Ed25519 tidak valid terhadap muatan atau kunci publik.';
        }

        $surat = $persetujuan->administrasi;
        $penyetuju = $persetujuan->penyetuju;
        if ($surat === null || $penyetuju === null || $persetujuan->STATUSPERSETUJUAN !== 'DISETUJUI') {
            $hasil['alasan'][] = 'Surat, penyetuju, atau status persetujuan tidak valid.';

            return $hasil;
        }

        try {
            $muatan = json_decode($persetujuan->MUATANTTD, true, 512, JSON_THROW_ON_ERROR);
            if (! is_array($muatan)) {
                throw new \TypeError('Muatan bukan objek JSON.');
            }

            $jsonKanonik = $this->pembekuIsiSurat->jsonKanonik($muatan);
        } catch (JsonException|\TypeError) {
            $hasil['alasan'][] = 'Muatan tanda tangan bukan JSON kanonik yang dapat dibaca.';

            return $hasil;
        }

        $hasil['muatan'] = hash_equals($jsonKanonik, $persetujuan->MUATANTTD)
            && (string) ($muatan['IDADMINISTRASI'] ?? '') === (string) $surat->IDADMINISTRASI
            && (string) ($muatan['NOMORSURAT'] ?? '') === (string) $surat->NOMORSURAT
            && (string) ($muatan['IDPENYETUJU'] ?? '') === (string) $persetujuan->IDPENYETUJU
            && (string) ($muatan['URUTAN'] ?? '') === (string) $persetujuan->URUTAN
            && is_string($muatan['WAKTU'] ?? null)
            && $persetujuan->WAKTUKEPUTUSAN !== null
            && strtotime($muatan['WAKTU']) === $persetujuan->WAKTUKEPUTUSAN->timestamp;

        if (! $hasil['muatan']) {
            $hasil['alasan'][] = 'Identitas surat, penyetuju, urutan, waktu, atau format muatan tidak cocok.';
        }

        $hashIsiAktual = hash('sha256', (string) $surat->ISISURATKANONIK);
        $hasil['hash_isi'] = $surat->HASHISI !== null
            && hash_equals($surat->HASHISI, $hashIsiAktual)
            && hash_equals($surat->HASHISI, (string) ($muatan['HASHISI'] ?? ''));

        if (! $hasil['hash_isi']) {
            $hasil['alasan'][] = 'Hash isi surat tidak cocok dengan ISISURATKANONIK atau hash dalam muatan.';
        }

        $hasil['valid'] = $hasil['tanda_tangan']
            && $hasil['sidik_jari']
            && $hasil['hash_isi']
            && $hasil['muatan'];

        return $hasil;
    }
}
