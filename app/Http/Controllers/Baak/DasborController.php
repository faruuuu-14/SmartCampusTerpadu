<?php

namespace App\Http\Controllers\Baak;

use App\Http\Controllers\Controller;
use App\Models\Administrasi;
use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\Pengguna;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

// ==========================================
// PEMBAHASAN / BAGIAN: Kontroler Dasbor Staf BAAK
// ==========================================
class DasborController extends Controller
{
    // ==========================================
    // PEMBAHASAN / BAGIAN: Menampilkan Beranda Dasbor BAAK
    // ==========================================
    public function indeks(): View
    {
        /** @var Pengguna $pengguna */
        $pengguna = Auth::user();
        $jumlahSuratMenunggu = Administrasi::query()
            ->whereIn('STATUSADMINISTRASI', ['DIAJUKAN', 'VERIFIKASIBAAK'])
            ->count();

        return view('baak.dasbor', [
            'pengguna' => $pengguna,
            'jumlahSuratMenunggu' => $jumlahSuratMenunggu,
            'jumlahMahasiswa' => Mahasiswa::query()->count(),
            'jumlahDosen' => Dosen::query()->count(),
            'peranAktif' => session('PERANAKTIF'),
            'daftarPeran' => $pengguna->daftarPeran(),
        ]);
    }
}
