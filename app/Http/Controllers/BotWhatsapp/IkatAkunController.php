<?php

namespace App\Http\Controllers\BotWhatsapp;

use App\Http\Controllers\Controller;
use App\Models\Pengguna;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\View\View;

// ==========================================
// PEMBAHASAN / BAGIAN: Pembuatan Kode Pengikatan Akun WhatsApp
// ==========================================
class IkatAkunController extends Controller
{
    // ==========================================
    // PEMBAHASAN / BAGIAN: Membuat Kode Sekali Pakai untuk Akun Login
    // ==========================================
    public function tampilkan(): View
    {
        /** @var Pengguna $pengguna */
        $pengguna = Auth::user();
        $kode = Str::upper(Str::random(8));
        Cache::put('whatsapp:binding:'.hash('sha256', $kode), $pengguna->IDPENGGUNA, now()->addMinutes(10));

        return view('whatsapp.ikat', [
            'kode' => $kode,
            'nomorTerikat' => $pengguna->NOMORTELEPON,
        ]);
    }
}
