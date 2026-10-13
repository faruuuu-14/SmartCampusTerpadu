<?php

namespace App\Http\Controllers\Autentikasi;

use App\Http\Controllers\Controller;
use App\Http\Requests\Autentikasi\LoginRequest;
use App\Models\Pengguna;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

// ==========================================
// PEMBAHASAN / BAGIAN: Kontroler Autentikasi Satu Pintu Masuk
// ==========================================
class AutentikasiController extends Controller
{
    // ==========================================
    // PEMBAHASAN / BAGIAN: Menampilkan Formulir Login
    // ==========================================
    public function tampilkanFormLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            /** @var Pengguna $pengguna */
            $pengguna = Auth::user();
            $peranAktif = session('PERANAKTIF');

            if ($peranAktif) {
                return $this->arahkanBerdasarkanPeran($peranAktif);
            }

            $daftarPeran = $pengguna->daftarPeran();
            if (count($daftarPeran) === 1) {
                session(['PERANAKTIF' => $daftarPeran[0]]);

                return $this->arahkanBerdasarkanPeran($daftarPeran[0]);
            }

            if (count($daftarPeran) > 1) {
                return redirect()->route('autentikasi.pilihperan');
            }
        }

        return view('autentikasi.login');
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Memproses Autentikasi Kredensial
    // ==========================================
    public function prosesLogin(LoginRequest $request): RedirectResponse
    {
        $kredensial = [
            'USERNAME' => $request->input('USERNAME'),
            'password' => $request->input('PASSWORD'),
            'STATUSAKUN' => 'AKTIF',
        ];

        if (! Auth::attempt($kredensial)) {
            return back()->withInput($request->only('USERNAME'))->withErrors([
                'USERNAME' => 'Username atau kata sandi tidak valid, atau akun sedang nonaktif.',
            ]);
        }

        $request->session()->regenerate();

        /** @var Pengguna $pengguna */
        $pengguna = Auth::user();
        $daftarPeran = $pengguna->daftarPeran();

        if (empty($daftarPeran)) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'USERNAME' => 'Akun Anda tidak memiliki peran aktif dalam sistem.',
            ]);
        }

        // Jika hanya memiliki satu peran, langsung arahkan ke dasbor terkait
        if (count($daftarPeran) === 1) {
            session(['PERANAKTIF' => $daftarPeran[0]]);

            return $this->arahkanBerdasarkanPeran($daftarPeran[0]);
        }

        // Jika memiliki banyak peran, arahkan ke pemilihan peran
        return redirect()->route('autentikasi.pilihperan');
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Menampilkan Halaman Pemilihan Peran
    // ==========================================
    public function tampilkanPilihPeran(): View|RedirectResponse
    {
        /** @var Pengguna $pengguna */
        $pengguna = Auth::user();
        $daftarPeran = $pengguna->daftarPeran();

        if (count($daftarPeran) <= 1) {
            $peran = $daftarPeran[0] ?? null;
            if ($peran) {
                session(['PERANAKTIF' => $peran]);

                return $this->arahkanBerdasarkanPeran($peran);
            }

            return redirect()->route('login');
        }

        return view('autentikasi.pilihperan', [
            'pengguna' => $pengguna,
            'daftarPeran' => $daftarPeran,
            'peranAktif' => session('PERANAKTIF'),
        ]);
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Menyimpan Peran Aktif yang Dipilih
    // ==========================================
    public function simpanPilihPeran(Request $request): RedirectResponse
    {
        $request->validate([
            'PERAN' => ['required', 'string'],
        ]);

        /** @var Pengguna $pengguna */
        $pengguna = Auth::user();
        $daftarPeran = $pengguna->daftarPeran();
        $peranPilihan = $request->input('PERAN');

        if (! in_array($peranPilihan, $daftarPeran, true)) {
            return back()->withErrors([
                'PERAN' => 'Peran yang Anda pilih tidak terdaftar untuk akun ini.',
            ]);
        }

        session(['PERANAKTIF' => $peranPilihan]);

        return $this->arahkanBerdasarkanPeran($peranPilihan);
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Keluar dari Sistem (Logout)
    // ==========================================
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Pembantu Pengalihan Rute Dasbor Peran
    // ==========================================
    protected function arahkanBerdasarkanPeran(string $peran): RedirectResponse
    {
        return match ($peran) {
            'MAHASISWA' => redirect()->route('mahasiswa.dasbor'),
            'DOSEN' => redirect()->route('dosen.dasbor'),
            'PIMPINAN' => redirect()->route('pimpinan.dasbor'),
            'BAAK' => redirect()->route('baak.dasbor'),
            'ORMAWA' => redirect()->route('ormawa.dasbor'),
            default => redirect()->route('login'),
        };
    }
}
