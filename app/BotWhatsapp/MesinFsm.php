<?php

namespace App\BotWhatsapp;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

// ==========================================
// PEMBAHASAN / BAGIAN: Mesin Finite State Machine Bot WhatsApp
// ==========================================
class MesinFsm
{
    private const TTL_MENIT = 10;

    private const DAFTAR_STATE = ['AWAL', 'MENU', 'JADWAL', 'ABSEN'];

    // ==========================================
    // PEMBAHASAN / BAGIAN: Memproses Pesan dan Memperbarui State Pengguna
    // ==========================================
    public function tangani(string $nomorTelepon, string $pesan): string
    {
        $kunci = $this->kunciState($nomorTelepon);
        $state = Cache::get($kunci, 'AWAL');

        if (! is_string($state) || ! in_array($state, self::DAFTAR_STATE, true)) {
            Log::channel('single')->warning('State bot WhatsApp tidak dikenal; state diatur ulang.', [
                'nomor_telepon' => $nomorTelepon,
            ]);

            $state = 'AWAL';
        }

        $perintah = Str::lower(trim($pesan));

        if ($perintah === 'menu') {
            $state = 'MENU';
            $balasan = (new HandlerAwal)->menu();
        } elseif ($perintah === 'batal') {
            $state = 'AWAL';
            $balasan = 'Permintaan dibatalkan. Kirim pesan apa pun untuk memulai kembali.';
        } else {
            $hasil = match ($state) {
                'AWAL' => ['state' => 'MENU', 'balasan' => (new HandlerAwal)->tangani()],
                'MENU' => (new HandlerMenu)->tangani($perintah),
                'JADWAL' => ['state' => 'JADWAL', 'balasan' => (new HandlerJadwal)->tangani()],
                'ABSEN' => ['state' => 'ABSEN', 'balasan' => (new HandlerAbsen)->tangani()],
            };
            $state = $hasil['state'];
            $balasan = $hasil['balasan'];
        }

        Cache::put($kunci, $state, now()->addMinutes(self::TTL_MENIT));

        return $balasan;
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Membentuk Kunci Cache State Berdasarkan Nomor
    // ==========================================
    public function kunciState(string $nomorTelepon): string
    {
        $nomorBersih = preg_replace('/\D+/', '', $nomorTelepon);

        if ($nomorBersih === null || $nomorBersih === '') {
            throw new \InvalidArgumentException('Nomor telepon WhatsApp harus berisi angka.');
        }

        return 'whatsapp:state:'.$nomorBersih;
    }
}
