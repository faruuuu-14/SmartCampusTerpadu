<?php

namespace App\Http\Middleware;

use App\Models\Pengguna;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

// ==========================================
// PEMBAHASAN / BAGIAN: Middleware Deteksi & Penegakan Peran Aktif Server
// ==========================================
class DeteksiPeran
{
    // ==========================================
    // PEMBAHASAN / BAGIAN: Penanganan Permintaan HTTP
    // ==========================================
    public function handle(Request $request, Closure $next, string ...$peranYangDiizinkan): Response
    {
        // Pastikan pengguna sudah terautentikasi
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        /** @var Pengguna $pengguna */
        $pengguna = Auth::user();

        // Periksa status akun
        if ($pengguna->STATUSAKUN !== 'AKTIF') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'USERNAME' => 'Akun Anda saat ini berstatus nonaktif. Silakan hubungi BAAK.',
            ]);
        }

        // Ambil peran aktif yang tersimpan pada sesi server
        $peranAktif = session('PERANAKTIF');
        $daftarPeran = $pengguna->daftarPeran();

        if (empty($daftarPeran)) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            abort(403, 'Akses Ditolak: Akun Anda tidak memiliki relasi peran aktif.');
        }

        // Jika peran aktif belum ditentukan di sesi
        if (! $peranAktif || ! in_array($peranAktif, $daftarPeran, true)) {
            if (count($daftarPeran) === 1) {
                $peranAktif = $daftarPeran[0];
                session(['PERANAKTIF' => $peranAktif]);
            } else {
                return redirect()->route('autentikasi.pilihperan');
            }
        }

        // Verifikasi izin peran terhadap rute yang dituju
        if (! empty($peranYangDiizinkan) && ! in_array($peranAktif, $peranYangDiizinkan, true)) {
            abort(403, sprintf(
                'Akses Ditolak: Halaman ini memerlukan peran [%s], sedangkan peran aktif Anda adalah [%s].',
                implode(', ', $peranYangDiizinkan),
                $peranAktif
            ));
        }

        return $next($request);
    }
}
