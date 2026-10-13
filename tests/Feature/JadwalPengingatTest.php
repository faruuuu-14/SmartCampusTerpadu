<?php

namespace Tests\Feature;

use App\BotWhatsapp\PengirimPesan;
use App\Layanan\PengingatJadwalLayanan;
use Database\Seeders\DataDemoSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

// ==========================================
// PEMBAHASAN / BAGIAN: Pengujian Antrean Pengingat Jadwal Kuliah
// ==========================================
class JadwalPengingatTest extends TestCase
{
    use DatabaseTransactions;

    // ==========================================
    // PEMBAHASAN / BAGIAN: Mengirim Pengingat Sesuai Menit dan Tanpa Duplikasi
    // ==========================================
    public function test_perintah_mengantrekan_pengingat_sesuai_menit_dan_tidak_mengulang(): void
    {
        $this->seed(DataDemoSeeder::class);
        $this->travelTo(Carbon::parse('2026-10-19 07:30:00'));
        $this->mock(PengirimPesan::class, function ($mock): void {
            $mock->shouldReceive('kirim')
                ->times(3)
                ->withArgs(fn (string $nomor, string $pesan): bool => str_contains($pesan, 'Rekayasa Perangkat Lunak'));
        });

        $layanan = app(PengingatJadwalLayanan::class);
        $this->assertSame(3, $layanan->jalankan());
        $this->assertSame(3, DB::table('ANTRIANPENGINGAT')->where('STATUSPENGINGAT', 'TERKIRIM')->count());
        $this->assertSame(0, $layanan->jalankan());

        $this->travelBack();
    }
}
