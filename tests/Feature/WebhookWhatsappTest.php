<?php

namespace Tests\Feature;

use App\BotWhatsapp\MesinFsm;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

// ==========================================
// PEMBAHASAN / BAGIAN: Pengujian Webhook dan Mesin Bot WhatsApp
// ==========================================
class WebhookWhatsappTest extends TestCase
{
    // ==========================================
    // PEMBAHASAN / BAGIAN: Verifikasi Webhook dengan Token Sah
    // ==========================================
    public function test_verifikasi_webhook_mengembalikan_tantangan_dengan_token_sah(): void
    {
        config(['whatsapp.verify_token' => 'token-verifikasi-uji']);

        $response = $this->get('/webhook/whatsapp?hub.mode=subscribe&hub.verify_token=token-verifikasi-uji&hub.challenge=tantangan-123');

        $response->assertOk();
        $response->assertSeeText('tantangan-123');
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Menolak Verifikasi dengan Token Tidak Sah
    // ==========================================
    public function test_verifikasi_webhook_menolak_token_yang_tidak_sah(): void
    {
        config(['whatsapp.verify_token' => 'token-benar']);

        $response = $this->get('/webhook/whatsapp?hub.mode=subscribe&hub.verify_token=token-salah&hub.challenge=tantangan-123');

        $response->assertForbidden();
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Menolak Pesan dengan Signature Tidak Sah
    // ==========================================
    public function test_webhook_menolak_pesan_tanpa_signature_yang_sah(): void
    {
        config(['whatsapp.app_secret' => 'rahasia-uji']);

        $response = $this->postJson('/webhook/whatsapp', ['entry' => []]);

        $response->assertForbidden();
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Memproses Pesan Sah dan Mencatat Balasan
    // ==========================================
    public function test_webhook_memproses_pesan_sah_dalam_mode_log(): void
    {
        $rahasia = 'rahasia-uji';
        $nomorTelepon = '6281234567890';
        $isiPesan = 'Halo';
        $isiPermintaan = json_encode([
            'entry' => [[
                'changes' => [[
                    'value' => [
                        'messages' => [[
                            'id' => 'wamid-uji-001',
                            'from' => $nomorTelepon,
                            'text' => ['body' => $isiPesan],
                        ]],
                    ],
                ]],
            ]],
        ], JSON_THROW_ON_ERROR);

        config([
            'whatsapp.app_secret' => $rahasia,
            'whatsapp.mode' => 'log',
            'cache.default' => 'array',
        ]);
        Cache::forget('whatsapp:state:'.$nomorTelepon);
        Log::shouldReceive('channel')->once()->with('single')->andReturnSelf();
        Log::shouldReceive('info')->once()->with('Simulasi pengiriman pesan WhatsApp.', [
            'nomor_telepon' => $nomorTelepon,
            'isi_pesan' => "Selamat datang di Bot Smart Campus Terpadu.\n\nPilih layanan berikut:\n1. Jadwal kuliah\n2. Informasi absensi\n\nKetik BATAL untuk membatalkan.",
            'tombol' => [],
        ]);

        $signature = 'sha256='.hash_hmac('sha256', $isiPermintaan, $rahasia);
        $response = $this->call(
            'POST',
            '/webhook/whatsapp',
            [],
            [],
            [],
            [
                'HTTP_ACCEPT' => 'application/json',
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X_HUB_SIGNATURE_256' => $signature,
            ],
            $isiPermintaan
        );

        $response->assertOk();
        $response->assertJsonPath('status', 'ok');
        $response->assertJsonPath('jumlah_pesan_diproses', 1);
        $response->assertJsonPath('jumlah_duplikat', 0);
        $this->assertSame('MENU', Cache::get('whatsapp:state:'.$nomorTelepon));
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Mengabaikan IDPESANWA yang Pernah Diproses
    // ==========================================
    public function test_webhook_tidak_memproses_id_pesan_yang_sama_dua_kali(): void
    {
        $rahasia = 'rahasia-uji';
        $isiPermintaan = json_encode([
            'entry' => [[
                'changes' => [[
                    'value' => [
                        'messages' => [[
                            'id' => 'wamid-uji-duplikat',
                            'from' => '6281234567892',
                            'text' => ['body' => 'Halo'],
                        ]],
                    ],
                ]],
            ]],
        ], JSON_THROW_ON_ERROR);
        config([
            'whatsapp.app_secret' => $rahasia,
            'whatsapp.mode' => 'log',
            'cache.default' => 'array',
        ]);
        Cache::flush();
        Log::shouldReceive('channel')->once()->with('single')->andReturnSelf();
        Log::shouldReceive('info')->once();

        $signature = 'sha256='.hash_hmac('sha256', $isiPermintaan, $rahasia);
        $konteks = [
            'HTTP_ACCEPT' => 'application/json',
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_HUB_SIGNATURE_256' => $signature,
        ];
        $pertama = $this->call('POST', '/webhook/whatsapp', [], [], [], $konteks, $isiPermintaan);
        $pertama->assertOk()->assertJsonPath('jumlah_pesan_diproses', 1);

        $kedua = $this->call('POST', '/webhook/whatsapp', [], [], [], $konteks, $isiPermintaan);
        $kedua->assertOk()
            ->assertJsonPath('jumlah_pesan_diproses', 0)
            ->assertJsonPath('jumlah_duplikat', 1);
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Menguji Perintah Universal dan Masa Berlaku State
    // ==========================================
    public function test_perintah_menu_batal_dan_ttl_state_sepuluh_menit(): void
    {
        $this->travelTo(Carbon::parse('2026-10-14 09:00:00'));
        config(['cache.default' => 'array']);
        $mesin = app(MesinFsm::class);
        $nomorTelepon = '6281234567891';
        $kunciState = $mesin->kunciState($nomorTelepon);
        Cache::forget($kunciState);

        $mesin->tangani($nomorTelepon, 'Halo');
        $this->assertSame('MENU', Cache::get($kunciState));

        $mesin->tangani($nomorTelepon, '2');
        $this->assertSame('ABSEN', Cache::get($kunciState));

        $mesin->tangani($nomorTelepon, 'MENU');
        $this->assertSame('MENU', Cache::get($kunciState));

        $mesin->tangani($nomorTelepon, 'batal');
        $this->assertSame('AWAL', Cache::get($kunciState));

        $this->travelBack();
        $mesin->tangani($nomorTelepon, 'Halo');
        $this->travel(9)->minutes();
        $this->assertTrue(Cache::has($kunciState));

        $this->travel(2)->minutes();
        $this->assertFalse(Cache::has($kunciState));
    }
}
