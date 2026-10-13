<?php

namespace App\Providers;

use App\Layanan\Presensi\PenilaiWajah;
use App\Layanan\Presensi\PenilaiWajahHttp;
use App\Layanan\Presensi\PenilaiWajahTiruan;
use Illuminate\Contracts\Foundation\Application;
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
            return match (config('presensi.penilai_wajah.mode')) {
                'stub' => $app->make(PenilaiWajahTiruan::class),
                'http' => $app->make(PenilaiWajahHttp::class),
                default => throw new InvalidArgumentException('PENILAI_WAJAH_MODE harus bernilai stub atau http.'),
            };
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
