<?php

namespace App\Console\Commands;

use App\Layanan\TandaTanganElektronik\KunciLayanan;
use App\Models\Pengguna;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

// ==========================================
// PEMBAHASAN / BAGIAN: Perintah Provisioning Kunci Penyetuju
// ==========================================
#[Signature('tte:buat-kunci')]
#[Description('Membuat pasangan kunci Ed25519 bagi dosen berjabatan yang belum memilikinya.')]
class TteBuatKunci extends Command
{
    // ==========================================
    // PEMBAHASAN / BAGIAN: Membuat Kunci untuk Seluruh Pimpinan
    // ==========================================
    public function handle(KunciLayanan $kunciLayanan): int
    {
        $dibuat = 0;
        $sudahAda = 0;

        Pengguna::query()
            ->whereHas('dosen', fn ($query) => $query->whereNotNull('JABATAN')->where('JABATAN', '<>', ''))
            ->with('dosen')
            ->orderBy('IDPENGGUNA')
            ->each(function (Pengguna $pengguna) use ($kunciLayanan, &$dibuat, &$sudahAda): void {
                if ($kunciLayanan->buatJikaBelumAda($pengguna)) {
                    $dibuat++;
                } else {
                    $sudahAda++;
                }
            });

        $this->info("Kunci baru: {$dibuat}; kunci yang sudah tersedia: {$sudahAda}.");

        return self::SUCCESS;
    }
}
