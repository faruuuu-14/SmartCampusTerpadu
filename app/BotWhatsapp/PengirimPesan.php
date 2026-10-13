<?php

namespace App\BotWhatsapp;

use Illuminate\Support\Facades\Log;
use LogicException;

// ==========================================
// PEMBAHASAN / BAGIAN: Pengirim Pesan Bot WhatsApp
// ==========================================
class PengirimPesan
{
    public function __construct(private readonly KlienWhatsAppCloudApi $klien) {}

    // ==========================================
    // PEMBAHASAN / BAGIAN: Mengirim atau Mencatat Pesan Balasan
    // ==========================================
    /**
     * @param  array<int, array{id: string, title: string}>  $tombol
     */
    public function kirim(string $nomorTelepon, string $pesan, array $tombol = []): void
    {
        if (config('whatsapp.mode') === 'cloud') {
            if ($tombol !== []) {
                $this->klien->kirimTombol($nomorTelepon, $pesan, $tombol);
            } else {
                $this->klien->kirimTeks($nomorTelepon, $pesan);
            }

            return;
        }

        if (config('whatsapp.mode') !== 'log') {
            throw new LogicException('Mode WhatsApp harus berupa log atau cloud.');
        }

        Log::channel('single')->info('Simulasi pengiriman pesan WhatsApp.', [
            'nomor_telepon' => $nomorTelepon,
            'isi_pesan' => $pesan,
            'tombol' => $tombol,
        ]);
    }
}
