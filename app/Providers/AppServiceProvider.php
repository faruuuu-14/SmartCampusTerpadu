<?php

namespace App\Providers;

use App\Layanan\Presensi\PenilaiWajah;
use App\Layanan\Presensi\PenilaiWajahLayanan;
use App\Layanan\Presensi\PenilaiWajahTiruan;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // ==========================================
        // PEMBAHASAN / BAGIAN: Pemilihan Penilai Wajah dari Konfigurasi
        // ==========================================
        $this->app->bind(PenilaiWajah::class, function (Application $app): PenilaiWajah {
            $mode = config('wajah.mode');

            if ($mode === 'uji') {
                Log::warning('WAJAH_MODE=uji aktif; skor tiruan hanya boleh digunakan untuk pengembangan.');

                return $app->make(PenilaiWajahTiruan::class);
            }

            if ($mode === 'layanan') {
                return $app->make(PenilaiWajahLayanan::class);
            }

            throw new InvalidArgumentException('WAJAH_MODE harus bernilai layanan atau uji.');
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
