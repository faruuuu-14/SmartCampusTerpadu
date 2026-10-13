<?php

namespace App\Http\Controllers\Baak;

use App\Http\Controllers\Controller;
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

        return view('baak.dasbor', [
            'pengguna' => $pengguna,
            'peranAktif' => session('PERANAKTIF'),
            'daftarPeran' => $pengguna->daftarPeran(),
        ]);
    }
}
