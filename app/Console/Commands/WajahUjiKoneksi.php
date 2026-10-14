<?php

// ==========================================
// PEMBAHASAN / BAGIAN: Uji Koneksi dan Kunci API Layanan Wajah
// ==========================================

namespace App\Console\Commands;

use App\Layanan\Presensi\KlienWajah;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Log;
use LogicException;

#[Signature('wajah:uji-koneksi')]
#[Description('Memeriksa koneksi, kunci API, dan model layanan wajah')]
class WajahUjiKoneksi extends Command
{
    public function handle(KlienWajah $klien): int
    {
        try {
            $respons = $klien->kesehatan();
        } catch (ConnectionException|RequestException|LogicException $kesalahan) {
            Log::warning('Pemeriksaan koneksi layanan wajah gagal.', [
                'alasan' => $kesalahan::class,
            ]);
            $this->error('Koneksi/kunci layanan wajah gagal: '.$kesalahan->getMessage());

            return self::FAILURE;
        }

        if (($respons['status'] ?? null) !== 'siap' || ($respons['stateless'] ?? null) !== true) {
            $this->error('Layanan merespons tetapi statusnya tidak sesuai.');

            return self::FAILURE;
        }

        $modelAktif = is_string($respons['model_aktif'] ?? null) ? $respons['model_aktif'] : 'tidak diketahui';
        $this->info("Layanan wajah siap, kunci API diterima, model aktif: {$modelAktif}.");

        return self::SUCCESS;
    }
}
