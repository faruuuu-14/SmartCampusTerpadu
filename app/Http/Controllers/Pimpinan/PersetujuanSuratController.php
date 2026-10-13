<?php

namespace App\Http\Controllers\Pimpinan;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pimpinan\SetujuiSuratRequest;
use App\Http\Requests\Pimpinan\TolakSuratRequest;
use App\Layanan\AlurPersetujuanSurat;
use App\Models\Pengguna;
use App\Models\PersetujuanSurat;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

// ==========================================
// PEMBAHASAN / BAGIAN: Persetujuan Surat Elektronik Pimpinan
// ==========================================
class PersetujuanSuratController extends Controller
{
    // ==========================================
    // PEMBAHASAN / BAGIAN: Daftar Hanya Giliran Persetujuan yang Aktif
    // ==========================================
    public function indeks(): View
    {
        $pelaku = auth()->user();
        abort_unless($pelaku instanceof Pengguna, 403);
        $daftar = $pelaku->persetujuanSurat()
            ->where('STATUSPERSETUJUAN', 'MENUNGGU')
            ->whereHas('administrasi', fn ($query) => $query->where('STATUSADMINISTRASI', 'MENUNGGUPERSETUJUAN'))
            ->with(['administrasi.pemohon.mahasiswa', 'administrasi.persetujuanSurat.penyetuju.dosen'])
            ->orderBy('TANGGALBUAT')
            ->get()
            ->filter(fn (PersetujuanSurat $item): bool => $this->giliranAktif($item))
            ->values();

        return view('pimpinan.persetujuan-surat', ['persetujuan' => $daftar]);
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Menandatangani Surat Setelah Autentikasi Ulang
    // ==========================================
    public function setujui(
        SetujuiSuratRequest $request,
        PersetujuanSurat $persetujuan,
        AlurPersetujuanSurat $alur
    ): RedirectResponse {
        $pelaku = $request->user();
        abort_unless($pelaku instanceof Pengguna, 403);
        $alur->setujui($persetujuan, $pelaku, $request->validated('PASSWORD'));

        return to_route('pimpinan.surat.indeks')->with('status', 'Tanda tangan elektronik berhasil direkam.');
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Menolak Surat dengan Catatan Wajib
    // ==========================================
    public function tolak(
        TolakSuratRequest $request,
        PersetujuanSurat $persetujuan,
        AlurPersetujuanSurat $alur
    ): RedirectResponse {
        $pelaku = $request->user();
        abort_unless($pelaku instanceof Pengguna, 403);
        $alur->tolak($persetujuan, $pelaku, $request->validated('CATATAN'));

        return to_route('pimpinan.surat.indeks')->with('status', 'Penolakan surat berhasil dicatat.');
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Menyaring Penyetuju yang Belum Mendapat Giliran
    // ==========================================
    private function giliranAktif(PersetujuanSurat $persetujuan): bool
    {
        $sebelumnya = $persetujuan->administrasi->persetujuanSurat
            ->first(fn (PersetujuanSurat $item): bool => $item->URUTAN < $persetujuan->URUTAN);

        return $sebelumnya === null || $sebelumnya->STATUSPERSETUJUAN === 'DISETUJUI';
    }
}
