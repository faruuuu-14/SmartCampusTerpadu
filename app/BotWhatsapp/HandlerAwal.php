<?php

namespace App\BotWhatsapp;

// ==========================================
// PEMBAHASAN / BAGIAN: Handler State AWAL Bot WhatsApp
// ==========================================
class HandlerAwal
{
    // ==========================================
    // PEMBAHASAN / BAGIAN: Menyambut Pengguna dan Menampilkan Menu
    // ==========================================
    public function tangani(): string
    {
        return "Selamat datang di Bot Smart Campus Terpadu.\n\n".$this->menu();
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Menyusun Pilihan Menu Bot
    // ==========================================
    public function menu(): string
    {
        return "Pilih layanan berikut:\n1. Jadwal kuliah\n2. Informasi absensi\n\nKetik BATAL untuk membatalkan.";
    }
}
