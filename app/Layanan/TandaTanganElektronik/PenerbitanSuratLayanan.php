<?php

namespace App\Layanan\TandaTanganElektronik;

use App\Models\Administrasi;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

// ==========================================
// PEMBAHASAN / BAGIAN: Penerbitan PDF Final Surat Bertanda Tangan
// ==========================================
class PenerbitanSuratLayanan
{
    // ==========================================
    // PEMBAHASAN / BAGIAN: Menyediakan Pembuat QR untuk PDF
    // ==========================================
    public function __construct(private QrLayanan $qrLayanan) {}

    // ==========================================
    // PEMBAHASAN / BAGIAN: Membuat PDF, Menyimpan QR, dan Menghitung Hash Berkas
    // ==========================================
    public function terbitkan(Administrasi $surat): void
    {
        $jalurBerkas = null;

        try {
            DB::transaction(function () use ($surat, &$jalurBerkas): void {
                $terkunci = Administrasi::query()
                    ->whereKey($surat->getKey())
                    ->lockForUpdate()
                    ->firstOrFail();
                $persetujuan = $terkunci->persetujuanSurat()
                    ->with('penyetuju.dosen')
                    ->orderBy('URUTAN')
                    ->get();

                if ($terkunci->STATUSADMINISTRASI !== 'MENUNGGUPERSETUJUAN'
                    || $persetujuan->count() < 2
                    || $persetujuan->contains(fn ($item): bool => $item->STATUSPERSETUJUAN !== 'DISETUJUI')) {
                    throw new RuntimeException('Surat belum memiliki dua persetujuan berurutan yang lengkap.');
                }

                $isiSurat = json_decode((string) $terkunci->ISISURATKANONIK, true, 512, JSON_THROW_ON_ERROR);
                $blokTandaTangan = $persetujuan->map(function ($item): array {
                    $url = route('tte.verifikasi', ['token' => $item->TOKENVERIFIKASI]);
                    $urlHttps = preg_replace('/^http:/i', 'https:', $url);

                    return [
                        'nama' => $item->penyetuju?->NAMALENGKAP ?? 'Penyetuju',
                        'jabatan' => $item->penyetuju?->dosen?->JABATAN ?? 'Pimpinan',
                        'waktu' => $item->WAKTUKEPUTUSAN?->format('d-m-Y H:i:s'),
                        'qr' => $this->qrLayanan->dataUri((string) $urlHttps),
                    ];
                })->all();
                $isiPdf = Pdf::loadView('pdf.surat', [
                    'isiSurat' => $isiSurat,
                    'blokTandaTangan' => $blokTandaTangan,
                ])->setPaper('a4')->output();
                $jalurBerkas = 'surat/terbit/'.str()->uuid().'.pdf';

                if (! Storage::disk('local')->put($jalurBerkas, $isiPdf)) {
                    throw new RuntimeException('PDF surat tidak dapat disimpan.');
                }

                $terkunci->forceFill([
                    'HASHBERKAS' => hash('sha256', $isiPdf),
                    'FILEAKHIR' => $jalurBerkas,
                    'TANGGALTERBIT' => now(),
                    'STATUSADMINISTRASI' => 'TERBIT',
                ])->save();
            });
        } catch (Throwable $exception) {
            if ($jalurBerkas !== null) {
                Storage::disk('local')->delete($jalurBerkas);
            }

            throw $exception;
        }
    }
}
