<?php

namespace App\Http\Controllers\BotWhatsapp;

use App\BotWhatsapp\LayananBotWhatsapp;
use App\BotWhatsapp\PengirimPesan;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

// ==========================================
// PEMBAHASAN / BAGIAN: Kontroler Webhook Bot WhatsApp
// ==========================================
class BotWhatsappController extends Controller
{
    // ==========================================
    // PEMBAHASAN / BAGIAN: Memverifikasi Langganan Webhook
    // ==========================================
    public function verifikasi(Request $request): Response
    {
        $tokenKonfigurasi = (string) config('whatsapp.verify_token');
        $tokenPermintaan = (string) $request->query('hub_verify_token', '');
        $mode = (string) $request->query('hub_mode', '');
        $tantangan = (string) $request->query('hub_challenge', '');

        if (
            $tokenKonfigurasi === ''
            || $mode !== 'subscribe'
            || $tantangan === ''
            || ! hash_equals($tokenKonfigurasi, $tokenPermintaan)
        ) {
            return response('Verifikasi webhook ditolak.', 403);
        }

        return response($tantangan)->header('Content-Type', 'text/plain');
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Memvalidasi Signature dan Memproses Pesan Masuk
    // ==========================================
    public function terima(Request $request, LayananBotWhatsapp $layanan, PengirimPesan $pengirim): JsonResponse
    {
        if (! $this->signatureValid($request)) {
            return response()->json(['pesan' => 'Signature webhook tidak valid.'], 403);
        }

        $daftarEntri = $request->input('entry', []);

        if (! is_array($daftarEntri)) {
            return response()->json(['pesan' => 'Struktur webhook tidak valid.'], 422);
        }

        $jumlahPesanDiproses = 0;
        $jumlahDuplikat = 0;

        foreach ($daftarEntri as $entri) {
            if (! is_array($entri) || ! is_array($entri['changes'] ?? [])) {
                return response()->json(['pesan' => 'Struktur webhook tidak valid.'], 422);
            }

            foreach ($entri['changes'] ?? [] as $perubahan) {
                if (! is_array($perubahan)) {
                    return response()->json(['pesan' => 'Struktur webhook tidak valid.'], 422);
                }

                $daftarPesan = data_get($perubahan, 'value.messages', []);
                if (! is_array($daftarPesan)) {
                    return response()->json(['pesan' => 'Struktur webhook tidak valid.'], 422);
                }

                foreach ($daftarPesan as $pesanMasuk) {
                    if (! is_array($pesanMasuk)) {
                        return response()->json(['pesan' => 'Struktur pesan webhook tidak valid.'], 422);
                    }

                    $nomorTelepon = (string) ($pesanMasuk['from'] ?? '');
                    $idPesan = (string) ($pesanMasuk['id'] ?? '');
                    $isiPesan = (string) (
                        data_get($pesanMasuk, 'text.body')
                        ?? data_get($pesanMasuk, 'button.text')
                        ?? data_get($pesanMasuk, 'interactive.button_reply.id')
                        ?? ''
                    );
                    $idMedia = data_get($pesanMasuk, 'document.id')
                        ?? data_get($pesanMasuk, 'image.id');

                    if ($nomorTelepon === '' || $idPesan === '') {
                        return response()->json(['pesan' => 'Nomor atau ID pesan webhook tidak tersedia.'], 422);
                    }

                    $kunciDuplikat = 'whatsapp:processed:'.hash('sha256', $idPesan);
                    if (! Cache::add($kunciDuplikat, true, now()->addDay())) {
                        $jumlahDuplikat++;

                        continue;
                    }

                    $kunciBatasLaju = 'whatsapp:rate:'.hash('sha256', $nomorTelepon);
                    if (RateLimiter::tooManyAttempts($kunciBatasLaju, 20)) {
                        Log::channel('single')->warning('Pesan WhatsApp ditahan karena batas laju nomor tercapai.');

                        continue;
                    }
                    RateLimiter::hit($kunciBatasLaju, 60);

                    if ($isiPesan === '' && $idMedia === null) {
                        $hasil = ['pesan' => 'Saat ini bot hanya dapat memproses pesan teks dan lampiran dokumen tugas.'];
                    } else {
                        $hasil = $layanan->tangani($nomorTelepon, $isiPesan, is_string($idMedia) ? $idMedia : null);
                    }

                    $pengirim->kirim($nomorTelepon, $hasil['pesan'], $hasil['tombol'] ?? []);
                    $jumlahPesanDiproses++;
                }
            }
        }

        return response()->json([
            'status' => 'ok',
            'jumlah_pesan_diproses' => $jumlahPesanDiproses,
            'jumlah_duplikat' => $jumlahDuplikat,
        ]);
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Memeriksa HMAC-SHA256 dari Isi Permintaan Asli
    // ==========================================
    private function signatureValid(Request $request): bool
    {
        $rahasiaAplikasi = (string) config('whatsapp.app_secret');
        $signature = (string) $request->header('X-Hub-Signature-256', '');

        if (
            $rahasiaAplikasi === ''
            || preg_match('/\Asha256=[a-f0-9]{64}\z/', $signature) !== 1
        ) {
            return false;
        }

        $signatureDiharapkan = hash_hmac('sha256', $request->getContent(), $rahasiaAplikasi);

        return hash_equals($signatureDiharapkan, substr($signature, 7));
    }
}
