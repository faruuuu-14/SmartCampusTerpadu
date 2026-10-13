<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Http\Requests\Mahasiswa\AjukanSuratRequest;
use App\Layanan\AdministrasiSuratLayanan;
use App\Models\Pengguna;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

// ==========================================
// PEMBAHASAN / BAGIAN: Formulir dan Riwayat Pengajuan Surat
// ==========================================
class PengajuanSuratController extends Controller
{
    public function indeks(): View
    {
        /** @var Pengguna $pengguna */
        $pengguna = Auth::user();
        $pengajuan = $pengguna->administrasi()
            ->orderByDesc('TANGGALBUAT')
            ->orderByDesc('IDADMINISTRASI')
            ->paginate(10);

        return view('mahasiswa.pengajuan-surat', [
            'pengajuan' => $pengajuan,
        ]);
    }

    public function simpan(
        AjukanSuratRequest $request,
        AdministrasiSuratLayanan $layanan,
    ): RedirectResponse {
        /** @var Pengguna $pengguna */
        $pengguna = Auth::user();
        $layanan->ajukan($pengguna, $request->validated());

        return to_route('mahasiswa.surat.indeks')->with('status', 'Pengajuan surat berhasil dikirim.');
    }
}
