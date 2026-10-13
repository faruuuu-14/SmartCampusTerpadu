<?php

namespace App\BotWhatsapp;

use Illuminate\Support\Facades\Log;
use LogicException;

// ==========================================
// PEMBAHASAN / BAGIAN: Pengirim Pesan Bot WhatsApp
// ==========================================
class PengirimPesan
{
    // ==========================================
    // PEMBAHASAN / BAGIAN: Mengirim atau Mencatat Pesan Balasan
    // ==========================================
    public function kirim(string $nomorTelepon, string $pesan): void
    {
        if (config('whatsapp.mode') !== 'log') {
            throw new LogicException('Mode pengiriman WhatsApp yang aktif belum didukung.');
        }

        Log::channel('single')->info('Simulasi pengiriman pesan WhatsApp.', [
            'nomor_telepon' => $nomorTelepon,
            'isi_pesan' => $pesan,
        ]);
    }
}
