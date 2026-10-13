<?php

namespace App\Http\Controllers\BotWhatsapp;

use App\BotWhatsapp\MesinFsm;
use App\BotWhatsapp\PengirimPesan;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

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
    public function terima(Request $request, MesinFsm $mesin, PengirimPesan $pengirim): JsonResponse
    {
        if (! $this->signatureValid($request)) {
            return response()->json(['pesan' => 'Signature webhook tidak valid.'], 403);
        }

        $daftarEntri = $request->input('entry', []);

        if (! is_array($daftarEntri)) {
            return response()->json(['pesan' => 'Struktur webhook tidak valid.'], 422);
        }

        $jumlahPesanDiproses = 0;

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
                    $isiPesan = (string) data_get($pesanMasuk, 'text.body', '');

                    if ($nomorTelepon === '') {
                        Log::channel('single')->warning('Pesan WhatsApp tanpa nomor pengirim diabaikan.');

                        continue;
                    }

                    if ($isiPesan === '') {
                        $balasan = 'Saat ini bot hanya dapat memproses pesan teks.';
                    } else {
                        $balasan = $mesin->tangani($nomorTelepon, $isiPesan);
                    }

                    $pengirim->kirim($nomorTelepon, $balasan);
                    $jumlahPesanDiproses++;
                }
            }
        }

        return response()->json([
            'status' => 'ok',
            'jumlah_pesan_diproses' => $jumlahPesanDiproses,
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
