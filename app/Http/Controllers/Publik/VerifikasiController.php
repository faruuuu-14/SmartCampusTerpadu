<?php

namespace App\Http\Controllers\Publik;

use App\Http\Controllers\Controller;
use App\Layanan\TandaTanganElektronik\PemverifikasiTte;
use App\Models\Administrasi;
use App\Models\PersetujuanSurat;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

// ==========================================
// PEMBAHASAN / BAGIAN: Verifikasi Publik TTE dan Keutuhan PDF
// ==========================================
class VerifikasiController extends Controller
{
    // ==========================================
    // PEMBAHASAN / BAGIAN: Menampilkan Seluruh Hasil Verifikasi Penandatangan
    // ==========================================
    public function tampilkan(string $token, PemverifikasiTte $pemverifikasi): View|Response
    {
        $persetujuanUtama = PersetujuanSurat::query()
            ->with(['administrasi.pemohon', 'administrasi.persetujuanSurat.penyetuju.dosen'])
            ->where('TOKENVERIFIKASI', $token)
            ->first();

        if ($persetujuanUtama === null) {
            return response()->view('publik.verifikasi', [
                'tokenDitemukan' => false,
                'hasilPenandatangan' => collect(),
                'perbandinganPdf' => null,
            ], 404);
        }

        $surat = $persetujuanUtama->administrasi;
        $hasilPenandatangan = $surat->persetujuanSurat
            ->sortBy('URUTAN')
            ->map(function (PersetujuanSurat $baris) use ($pemverifikasi): array {
                return [
                    'persetujuan' => $baris,
                    'hasil' => $pemverifikasi->periksa($baris),
                ];
            })
            ->values();

        return view('publik.verifikasi', [
            'tokenDitemukan' => true,
            'surat' => $surat,
            'persetujuanUtama' => $persetujuanUtama,
            'hasilPenandatangan' => $hasilPenandatangan,
            'validBerkasSistem' => $this->berkasSistemValid($surat),
            'perbandinganPdf' => session()->get('perbandingan_pdf'),
        ]);
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Membandingkan PDF Unggahan dengan HASHBERKAS
    // ==========================================
    public function bandingkan(Request $request, string $token): RedirectResponse
    {
        $data = $request->validate([
            'BERKASPDF' => ['required', 'file', 'mimes:pdf', 'max:20480'],
        ]);
        $persetujuan = PersetujuanSurat::query()
            ->with('administrasi')
            ->where('TOKENVERIFIKASI', $token)
            ->first();

        if ($persetujuan === null || $persetujuan->administrasi?->HASHBERKAS === null) {
            return to_route('tte.verifikasi', $token)->withErrors([
                'BERKASPDF' => 'Token atau hash PDF terbit tidak ditemukan.',
            ]);
        }

        $hashUnggahan = hash_file('sha256', $data['BERKASPDF']->getRealPath());
        $hashTersimpan = $persetujuan->administrasi->HASHBERKAS;

        return to_route('tte.verifikasi', $token)->with('perbandingan_pdf', [
            'hasil' => hash_equals($hashTersimpan, $hashUnggahan) ? 'SAMA' : 'BERBEDA',
            'hash_unggahan' => $hashUnggahan,
            'hash_terbit' => $hashTersimpan,
        ]);
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Memeriksa Berkas PDF Final pada Penyimpanan Privat
    // ==========================================
    private function berkasSistemValid(?Administrasi $surat): bool
    {
        if ($surat?->STATUSADMINISTRASI !== 'TERBIT'
            || ! $surat->FILEAKHIR
            || ! $surat->HASHBERKAS
            || ! Storage::disk('local')->exists($surat->FILEAKHIR)) {
            return false;
        }

        return hash_equals(
            $surat->HASHBERKAS,
            hash_file('sha256', Storage::disk('local')->path($surat->FILEAKHIR))
        );
    }
}
