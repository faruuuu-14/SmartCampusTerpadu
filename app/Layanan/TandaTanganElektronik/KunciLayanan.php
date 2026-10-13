<?php

namespace App\Layanan\TandaTanganElektronik;

use App\Models\Pengguna;
use Illuminate\Support\Facades\Crypt;
use RuntimeException;

// ==========================================
// PEMBAHASAN / BAGIAN: Pembuatan dan Penyimpanan Kunci Ed25519
// ==========================================
class KunciLayanan
{
    // ==========================================
    // PEMBAHASAN / BAGIAN: Membuat Kunci untuk Pengguna yang Belum Memilikinya
    // ==========================================
    public function buatJikaBelumAda(Pengguna $pengguna): bool
    {
        if ($pengguna->KUNCIPUBLIK !== null && $pengguna->KUNCIPRIVAT !== null) {
            return false;
        }

        if ($pengguna->KUNCIPUBLIK !== null || $pengguna->KUNCIPRIVAT !== null) {
            throw new RuntimeException('Pasangan kunci pengguna tidak lengkap; perbaiki secara manual.');
        }

        if (! function_exists('sodium_crypto_sign_keypair')) {
            throw new RuntimeException('Ekstensi Sodium tidak tersedia.');
        }

        $pasangan = sodium_crypto_sign_keypair();
        $publik = sodium_crypto_sign_publickey($pasangan);
        $privat = sodium_crypto_sign_secretkey($pasangan);
        sodium_memzero($pasangan);

        $pengguna->forceFill([
            'KUNCIPUBLIK' => base64_encode($publik),
            'KUNCIPRIVAT' => Crypt::encryptString(base64_encode($privat)),
        ])->save();

        sodium_memzero($privat);

        return true;
    }
}
