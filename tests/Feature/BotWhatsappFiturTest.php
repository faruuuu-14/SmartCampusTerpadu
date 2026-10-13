<?php

namespace Tests\Feature;

use App\BotWhatsapp\LayananBotWhatsapp;
use App\Models\Jadwal;
use App\Models\Pengguna;
use App\Models\Pengumpulan;
use App\Models\Tugas;
use Database\Seeders\DataDemoSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

// ==========================================
// PEMBAHASAN / BAGIAN: Pengujian Pengikatan Akun dan Alur Tugas WhatsApp
// ==========================================
class BotWhatsappFiturTest extends TestCase
{
    use DatabaseTransactions;

    // ==========================================
    // PEMBAHASAN / BAGIAN: Menyiapkan Akun Demo dan Cache Isolasi
    // ==========================================
    protected function setUp(): void
    {
        parent::setUp();
        config(['cache.default' => 'array']);
        Cache::flush();
        Storage::fake('local');
        $this->seed(DataDemoSeeder::class);
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Mengikat Nomor melalui Kode Satu Kali dari Web
    // ==========================================
    public function test_kode_pengikatan_dari_web_hanya_dapat_digunakan_satu_kali(): void
    {
        $pengguna = Pengguna::query()->where('USERNAME', '220101001')->firstOrFail();
        $this->actingAs($pengguna)->get(route('whatsapp.ikat'))
            ->assertOk()
            ->assertSeeText('BIND');
        preg_match('/BIND ([A-Z0-9]{8})/', $this->get(route('whatsapp.ikat'))->getContent(), $cocok);
        $this->assertNotEmpty($cocok[1] ?? null);

        $layanan = app(LayananBotWhatsapp::class);
        $hasil = $layanan->tangani('6281999990001', 'BIND '.$cocok[1]);
        $this->assertStringContainsString('berhasil diikat', $hasil['pesan']);
        $this->assertSame('6281999990001', $pengguna->fresh()->NOMORTELEPON);
        $this->assertNotNull($pengguna->fresh()->TANGGALBINDING);

        $hasilKedua = $layanan->tangani('6281999990001', 'BIND '.$cocok[1]);
        $this->assertStringContainsString('tidak valid atau sudah kedaluwarsa', $hasilKedua['pesan']);
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Mengunggah Berkas Tugas dari Media WhatsApp
    // ==========================================
    public function test_berkas_tugas_dari_whatsapp_disimpan_ke_pengumpulan(): void
    {
        config([
            'whatsapp.access_token' => 'token-uji',
            'whatsapp.api_base_url' => 'https://graph.facebook.com',
            'whatsapp.api_version' => 'v23.0',
        ]);
        Http::fake([
            'graph.facebook.com/v23.0/media-uji-123' => Http::response([
                'url' => 'https://media.example.test/tugas.pdf',
                'mime_type' => 'application/pdf',
            ]),
            'media.example.test/tugas.pdf' => Http::response('%PDF-1.7 tugas uji', 200, ['Content-Type' => 'application/pdf']),
        ]);
        $pengguna = Pengguna::query()->where('USERNAME', '220101001')->firstOrFail();
        $mahasiswa = $pengguna->mahasiswa;
        $jadwal = Jadwal::query()->whereHas('krs', fn ($query) => $query->where('IDMAHASISWA', $mahasiswa->IDMAHASISWA))->firstOrFail();
        $tugas = Tugas::query()->create([
            'IDJADWAL' => $jadwal->IDJADWAL,
            'IDDOSEN' => $jadwal->IDDOSEN,
            'JUDULTUGAS' => 'Tugas Bot',
            'TANGGALTENGGAT' => now()->addDay(),
            'MODEPENGERJAAN' => 'BEBAS',
            'DIBUATVIA' => 'WEB',
        ]);

        $layanan = app(LayananBotWhatsapp::class);
        $layanan->tangani($pengguna->NOMORTELEPON, 'tugas');
        $hasilAwal = $layanan->tangani($pengguna->NOMORTELEPON, 'SUDAH:'.$tugas->IDTUGAS);
        $this->assertStringContainsString('Kirim dokumen tugas', $hasilAwal['pesan']);
        $hasil = $layanan->tangani($pengguna->NOMORTELEPON, '', 'media-uji-123');
        $this->assertStringContainsString('tersimpan', $hasil['pesan']);

        $pengumpulan = Pengumpulan::query()
            ->where('IDTUGAS', $tugas->IDTUGAS)
            ->where('IDMAHASISWA', $mahasiswa->IDMAHASISWA)
            ->firstOrFail();
        $this->assertSame('SUDAH', $pengumpulan->STATUSPENGUMPULAN);
        $this->assertSame(hash('sha256', '%PDF-1.7 tugas uji'), $pengumpulan->HASHFILE);
        $this->assertStringContainsString('tersimpan', $hasil['pesan']);
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Membuat dan Menyiarkan Tugas Kilat Dosen
    // ==========================================
    public function test_dosen_dapat_membuat_tugas_kilat_untuk_jadwal_miliknya(): void
    {
        $pengguna = Pengguna::query()->where('USERNAME', 'DOSEN001')->firstOrFail();
        $jadwal = $pengguna->dosen->jadwal()->firstOrFail();
        $layanan = app(LayananBotWhatsapp::class);
        $nomor = $pengguna->NOMORTELEPON;

        $this->assertStringContainsString('ID jadwal', $layanan->tangani($nomor, 'tugas kilat')['pesan']);
        $this->assertStringContainsString('judul tugas', $layanan->tangani($nomor, (string) $jadwal->IDJADWAL)['pesan']);
        $this->assertStringContainsString('tenggat', $layanan->tangani($nomor, 'Tugas Demo Bot')['pesan']);
        $this->assertStringContainsString(
            'tersimpan dan disiarkan',
            $layanan->tangani($nomor, '2099-01-01 15:30')['pesan'],
        );

        $this->assertDatabaseHas('TUGAS', [
            'IDJADWAL' => $jadwal->IDJADWAL,
            'JUDULTUGAS' => 'Tugas Demo Bot',
            'DIBUATVIA' => 'BOT',
        ]);
    }
}
