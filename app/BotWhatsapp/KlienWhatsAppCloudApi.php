<?php

namespace App\BotWhatsapp;

use Illuminate\Support\Facades\Http;
use LogicException;

// ==========================================
// PEMBAHASAN / BAGIAN: Klien HTTP WhatsApp Cloud API
// ==========================================
class KlienWhatsAppCloudApi
{
    // ==========================================
    // PEMBAHASAN / BAGIAN: Mengirim Permintaan Teks ke Graph API
    // ==========================================
    public function kirimTeks(string $nomorTelepon, string $pesan): void
    {
        $this->permintaan('POST', $this->idNomor().'/messages', [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $nomorTelepon,
            'type' => 'text',
            'text' => ['preview_url' => false, 'body' => $pesan],
        ]);
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Mengirim Tombol Balasan Interaktif
    // ==========================================
    /**
     * @param  array<int, array{id: string, title: string}>  $tombol
     */
    public function kirimTombol(string $nomorTelepon, string $pesan, array $tombol): void
    {
        $baris = array_slice(array_map(fn (array $tombol): array => [
            'type' => 'reply',
            'reply' => ['id' => $tombol['id'], 'title' => mb_substr($tombol['title'], 0, 20)],
        ], $tombol), 0, 3);

        $this->permintaan('POST', $this->idNomor().'/messages', [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $nomorTelepon,
            'type' => 'interactive',
            'interactive' => [
                'type' => 'button',
                'body' => ['text' => $pesan],
                'action' => ['buttons' => $baris],
            ],
        ]);
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Mengunduh Berkas Media Masuk dari Cloud API
    // ==========================================
    /**
     * @return array{isi: string, tipe: string}
     */
    public function unduhMedia(string $idMedia): array
    {
        $metadata = $this->permintaan('GET', $idMedia);
        $url = $metadata['url'] ?? null;
        if (! is_string($url) || ! str_starts_with($url, 'https://')) {
            throw new LogicException('WhatsApp Cloud API tidak mengembalikan URL media HTTPS yang valid.');
        }

        $respons = Http::withToken($this->token())
            ->timeout($this->timeout())
            ->get($url)
            ->throw();

        return [
            'isi' => $respons->body(),
            'tipe' => (string) ($metadata['mime_type'] ?? $respons->header('Content-Type', 'application/octet-stream')),
        ];
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Mengirim Permintaan HTTP Terautentikasi
    // ==========================================
    /**
     * @param  array<string, mixed>|null  $data
     * @return array<string, mixed>
     */
    private function permintaan(string $metode, string $jalur, ?array $data = null): array
    {
        $respons = Http::withToken($this->token())
            ->acceptJson()
            ->timeout($this->timeout())
            ->send(
                $metode,
                rtrim((string) config('whatsapp.api_base_url'), '/').'/'.trim((string) config('whatsapp.api_version'), '/').'/'.ltrim($jalur, '/'),
                $data === null ? [] : ['json' => $data]
            )
            ->throw()
            ->json();

        if (! is_array($respons)) {
            throw new LogicException('Respons WhatsApp Cloud API tidak berformat JSON objek.');
        }

        return $respons;
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Memastikan Konfigurasi Rahasia Cloud API Tersedia
    // ==========================================
    private function token(): string
    {
        $token = (string) config('whatsapp.access_token');
        if ($token === '') {
            throw new LogicException('WHATSAPP_ACCESS_TOKEN belum dikonfigurasi.');
        }

        return $token;
    }

    private function idNomor(): string
    {
        $id = (string) config('whatsapp.phone_number_id');
        if ($id === '') {
            throw new LogicException('WHATSAPP_PHONE_NUMBER_ID belum dikonfigurasi.');
        }

        return $id;
    }

    private function timeout(): int
    {
        return max(1, (int) config('whatsapp.api_timeout', 15));
    }
}
