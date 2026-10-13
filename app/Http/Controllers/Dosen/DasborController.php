<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Pengguna;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

// ==========================================
// PEMBAHASAN / BAGIAN: Kontroler Dasbor Dosen
// ==========================================
class DasborController extends Controller
{
    // ==========================================
    // PEMBAHASAN / BAGIAN: Menampilkan Beranda Dasbor Dosen
    // ==========================================
    public function indeks(): View
    {
        /** @var Pengguna $pengguna */
        $pengguna = Auth::user();
        $dosen = $pengguna->dosen;
        $jumlahJadwalDiampu = $dosen?->jadwal()->count() ?? 0;
        $jumlahMahasiswaBimbingan = $dosen?->mahasiswaBimbingan()->count() ?? 0;

        return view('dosen.dasbor', [
            'pengguna' => $pengguna,
            'dosen' => $dosen,
            'jumlahJadwalDiampu' => $jumlahJadwalDiampu,
            'jumlahMahasiswaBimbingan' => $jumlahMahasiswaBimbingan,
            'peranAktif' => session('PERANAKTIF'),
            'daftarPeran' => $pengguna->daftarPeran(),
        ]);
    }
}
