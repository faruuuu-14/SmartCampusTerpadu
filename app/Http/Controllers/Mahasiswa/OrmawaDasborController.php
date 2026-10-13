<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Organisasi;
use App\Models\Pengguna;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

// ==========================================
// PEMBAHASAN / BAGIAN: Kontroler Dasbor Pengurus Ormawa
// ==========================================
class OrmawaDasborController extends Controller
{
    // ==========================================
    // PEMBAHASAN / BAGIAN: Menampilkan Beranda Dasbor Ormawa
    // ==========================================
    public function indeks(): View
    {
        /** @var Pengguna $pengguna */
        $pengguna = Auth::user();
        $mahasiswa = $pengguna->mahasiswa;

        $organisasi = $mahasiswa ? Organisasi::where('IDKETUA', $mahasiswa->IDMAHASISWA)->first() : null;

        return view('ormawa.dasbor', [
            'pengguna' => $pengguna,
            'mahasiswa' => $mahasiswa,
            'organisasi' => $organisasi,
            'peranAktif' => session('PERANAKTIF'),
            'daftarPeran' => $pengguna->daftarPeran(),
        ]);
    }
}
