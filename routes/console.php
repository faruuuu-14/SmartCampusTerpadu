<?php

use App\Layanan\PengingatJadwalLayanan;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ==========================================
// PEMBAHASAN / BAGIAN: Mengirim Pengingat Jadwal Kuliah secara Bertahap
// ==========================================
Artisan::command('jadwal:pengingat', function (PengingatJadwalLayanan $layanan): void {
    $jumlah = $layanan->jalankan();
    $this->info("Pengingat kuliah terkirim: {$jumlah}.");
})->purpose('Antre dan kirim pengingat kuliah WhatsApp');

Schedule::command('jadwal:pengingat')->everyMinute()->withoutOverlapping();
