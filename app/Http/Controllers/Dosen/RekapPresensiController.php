<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Dosen;
use App\Models\Pengguna;
use App\Models\Presensi;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

// ==========================================
// PEMBAHASAN / BAGIAN: Rekap Presensi Kelas bagi Dosen
// ==========================================
class RekapPresensiController extends Controller
{
    // ==========================================
    // PEMBAHASAN / BAGIAN: Menampilkan Rekap dan Foto Presensi Milik Dosen
    // ==========================================
    public function indeks(): View
    {
        $pengguna = auth()->user();
        abort_unless($pengguna instanceof Pengguna && $pengguna->dosen instanceof Dosen, 403);

        $jadwal = $pengguna->dosen->jadwal()
            ->with(['pertemuan.presensi.mahasiswa.pengguna'])
            ->orderBy('HARI')
            ->orderBy('JAMMULAI')
            ->get();

        return view('dosen.rekap-presensi', compact('jadwal'));
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Menyajikan Foto Presensi dari Penyimpanan Privat
    // ==========================================
    public function foto(Presensi $presensi): StreamedResponse
    {
        $pengguna = auth()->user();
        abort_unless($pengguna instanceof Pengguna && $pengguna->dosen instanceof Dosen, 403);
        abort_unless(
            $presensi->pertemuan()
                ->whereHas('jadwal', fn ($query) => $query->where('IDDOSEN', $pengguna->dosen->IDDOSEN))
                ->exists(),
            403
        );

        if (! $presensi->FILEFOTO || ! Storage::disk('local')->exists($presensi->FILEFOTO)) {
            throw new NotFoundHttpException('Foto presensi tidak ditemukan.');
        }

        return Storage::disk('local')->response(
            $presensi->FILEFOTO,
            null,
            ['Content-Type' => 'image/jpeg', 'Cache-Control' => 'private, no-store']
        );
    }
}
