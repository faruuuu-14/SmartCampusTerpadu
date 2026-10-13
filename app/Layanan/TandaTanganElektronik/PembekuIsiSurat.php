<?php

namespace App\Layanan\TandaTanganElektronik;

use App\Models\Administrasi;
use Illuminate\Support\Facades\DB;
use JsonException;
use RuntimeException;

// ==========================================
// PEMBAHASAN / BAGIAN: Pembekuan Isi Surat sebagai JSON Kanonik
// ==========================================
class PembekuIsiSurat
{
    // ==========================================
    // PEMBAHASAN / BAGIAN: Menerbitkan Nomor dan Membekukan Isi Surat
    // ==========================================
    public function bekukan(Administrasi $surat): void
    {
        DB::transaction(function () use ($surat): void {
            $terkunci = Administrasi::query()
                ->whereKey($surat->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($terkunci->ISISURATKANONIK !== null || $terkunci->HASHISI !== null) {
                throw new RuntimeException('Isi surat sudah dibekukan dan tidak dapat diubah.');
            }

            if ($terkunci->NOMORSURAT === null) {
                $terkunci->NOMORSURAT = sprintf(
                    'SCT/%s/%06d',
                    now()->format('Y'),
                    $terkunci->IDADMINISTRASI
                );
            }

            $terkunci->loadMissing(['pemohon.mahasiswa']);
            $mahasiswa = $terkunci->pemohon?->mahasiswa;
            $isi = [
                'DATAFORMULIR' => $terkunci->DATAFORMULIR ?? [],
                'JENISSURAT' => $terkunci->JENISSURAT,
                'KEPERLUAN' => $terkunci->KEPERLUAN,
                'NAMA' => $terkunci->pemohon?->NAMALENGKAP,
                'NIM' => $mahasiswa?->NIM,
                'NOMORSURAT' => $terkunci->NOMORSURAT,
                'PERIHAL' => $terkunci->PERIHAL,
                'PROGRAMSTUDI' => $mahasiswa?->PROGRAMSTUDI,
                'TANGGAL' => now()->toDateString(),
            ];
            $json = $this->jsonKanonik($isi);

            $terkunci->forceFill([
                'ISISURATKANONIK' => $json,
                'HASHISI' => hash('sha256', $json),
                'STATUSADMINISTRASI' => 'MENUNGGUPERSETUJUAN',
            ])->save();
        });
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Mengurutkan Kunci JSON secara Rekursif
    // ==========================================
    public function jsonKanonik(array $isi): string
    {
        try {
            return json_encode(
                $this->urutkanKunci($isi),
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
            );
        } catch (JsonException $exception) {
            throw new RuntimeException('Isi surat tidak dapat dijadikan JSON kanonik.', previous: $exception);
        }
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Menjaga Urutan Daftar dan Mengurutkan Objek
    // ==========================================
    private function urutkanKunci(array $nilai): array
    {
        foreach ($nilai as $kunci => $anak) {
            if (is_array($anak)) {
                $nilai[$kunci] = $this->urutkanKunci($anak);
            }
        }

        if (! array_is_list($nilai)) {
            ksort($nilai, SORT_STRING);
        }

        return $nilai;
    }
}
