<?php

namespace App\Http\Controllers;

use App\Layanan\TandaTanganElektronik\PemverifikasiTte;
use App\Models\PersetujuanSurat;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

// ==========================================
// PEMBAHASAN / BAGIAN: Pemeriksaan Publik TTE
// ==========================================
class VerifikasiTteController extends Controller
{
    // ==========================================
    // PEMBAHASAN / BAGIAN: Menampilkan Hasil Verifikasi Tanda Tangan dan PDF
    // ==========================================
    public function tampilkan(string $token, PemverifikasiTte $pemverifikasi): View
    {
        $persetujuan = PersetujuanSurat::query()
            ->with(['penyetuju.dosen', 'administrasi'])
            ->where('TOKENVERIFIKASI', $token)
            ->firstOrFail();
        $validTandaTangan = $pemverifikasi->verifikasi($persetujuan);
        $surat = $persetujuan->administrasi;
        $validBerkas = $surat?->STATUSADMINISTRASI === 'TERBIT'
            && $surat->FILEAKHIR
            && $surat->HASHBERKAS
            && Storage::disk('local')->exists($surat->FILEAKHIR)
            && hash_equals(
                $surat->HASHBERKAS,
                hash_file('sha256', Storage::disk('local')->path($surat->FILEAKHIR))
            );

        return view('verifikasi.tte', [
            'persetujuan' => $persetujuan,
            'surat' => $surat,
            'validTandaTangan' => $validTandaTangan,
            'validBerkas' => (bool) $validBerkas,
        ]);
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Mengunduh PDF Setelah Pemeriksaan Hash
    // ==========================================
    public function unduh(string $token, PemverifikasiTte $pemverifikasi): StreamedResponse
    {
        $persetujuan = PersetujuanSurat::query()
            ->with(['penyetuju', 'administrasi'])
            ->where('TOKENVERIFIKASI', $token)
            ->firstOrFail();
        $surat = $persetujuan->administrasi;
        $hashCocok = $pemverifikasi->verifikasi($persetujuan)
            && $surat?->STATUSADMINISTRASI === 'TERBIT'
            && $surat->FILEAKHIR
            && $surat->HASHBERKAS
            && Storage::disk('local')->exists($surat->FILEAKHIR)
            && hash_equals($surat->HASHBERKAS, hash_file('sha256', Storage::disk('local')->path($surat->FILEAKHIR)));

        abort_unless($hashCocok, 404);

        return Storage::disk('local')->download($surat->FILEAKHIR);
    }
}
