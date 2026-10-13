<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Krs;
use App\Models\Pengguna;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

// ==========================================
// PEMBAHASAN / BAGIAN: Kontroler Dasbor Mahasiswa
// ==========================================
class DasborController extends Controller
{
    // ==========================================
    // PEMBAHASAN / BAGIAN: Menampilkan Beranda Dasbor Mahasiswa
    // ==========================================
    public function indeks(): View
    {
        /** @var Pengguna $pengguna */
        $pengguna = Auth::user();
        $mahasiswa = $pengguna->mahasiswa;
        $urutanHari = ['SENIN', 'SELASA', 'RABU', 'KAMIS', 'JUMAT', 'SABTU', 'MINGGU'];
        $hariIni = $urutanHari[(int) now()->format('N') - 1];
        $jadwalHariIni = $mahasiswa
            ? Krs::query()
                ->where('IDMAHASISWA', $mahasiswa->IDMAHASISWA)
                ->whereHas('jadwal', fn ($query) => $query->where('HARI', $hariIni))
                ->with('jadwal')
                ->get()
                ->sortBy(fn (Krs $krs) => $krs->jadwal?->JAMMULAI)
                ->values()
            : collect();
        $jumlahPresensi = $mahasiswa?->presensi()->count() ?? 0;
        $jumlahHadir = $mahasiswa?->presensi()->where('STATUSPRESENSI', 'HADIR')->count() ?? 0;

        return view('mahasiswa.dasbor', [
            'pengguna' => $pengguna,
            'mahasiswa' => $mahasiswa,
            'hariIni' => $hariIni,
            'jadwalHariIni' => $jadwalHariIni,
            'jumlahPresensi' => $jumlahPresensi,
            'jumlahHadir' => $jumlahHadir,
            'peranAktif' => session('PERANAKTIF'),
            'daftarPeran' => $pengguna->daftarPeran(),
        ]);
    }
}
