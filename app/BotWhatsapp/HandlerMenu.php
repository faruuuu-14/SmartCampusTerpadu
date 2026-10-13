<?php

namespace App\BotWhatsapp;

// ==========================================
// PEMBAHASAN / BAGIAN: Handler State MENU Bot WhatsApp
// ==========================================
class HandlerMenu
{
    // ==========================================
    // PEMBAHASAN / BAGIAN: Memproses Pilihan Menu Utama
    // ==========================================
    /**
     * @return array{state: string, balasan: string}
     */
    public function tangani(string $pesan): array
    {
        return match (trim($pesan)) {
            '1' => [
                'state' => 'JADWAL',
                'balasan' => (new HandlerJadwal)->tangani(),
            ],
            '2' => [
                'state' => 'ABSEN',
                'balasan' => (new HandlerAbsen)->tangani(),
            ],
            default => [
                'state' => 'MENU',
                'balasan' => "Pilihan tidak dikenali.\n\n".(new HandlerAwal)->menu(),
            ],
        };
    }
}
