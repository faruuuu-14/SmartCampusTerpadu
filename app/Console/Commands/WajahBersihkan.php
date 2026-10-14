<?php

// ==========================================
// PEMBAHASAN / BAGIAN: Pembersihan Foto Presensi Sesuai Retensi
// ==========================================

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

#[Signature('wajah:bersihkan {--hari= : Retensi foto presensi dalam hari, menimpa konfigurasi}')]
#[Description('Menghapus foto presensi yang melewati masa retensi tanpa menghapus catatan presensi')]
class WajahBersihkan extends Command
{
    public function handle(): int
    {
        $hari = $this->option('hari') ?? config('wajah.retensi_hari');

        if (! is_numeric($hari) || (int) $hari < 1) {
            $this->error('Atur WAJAH_RETENSI_HARI atau berikan --hari dengan nilai minimal 1.');

            return self::FAILURE;
        }

        $batasWaktu = now()->subDays((int) $hari)->getTimestamp();
        $penyimpanan = Storage::disk('local');
        $jumlahDihapus = 0;

        foreach ($penyimpanan->allFiles('presensi') as $jalur) {
            if ($penyimpanan->lastModified($jalur) < $batasWaktu) {
                if (! $penyimpanan->delete($jalur)) {
                    throw new RuntimeException('Foto presensi lama gagal dihapus dari penyimpanan privat.');
                }
                $jumlahDihapus++;
            }
        }

        $this->info("Foto presensi lama yang dihapus: {$jumlahDihapus} (retensi {$hari} hari).");

        return self::SUCCESS;
    }
}
