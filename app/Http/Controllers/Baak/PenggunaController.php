<?php

namespace App\Http\Controllers\Baak;

use App\Http\Controllers\Controller;
use App\Http\Requests\Baak\SimpanPenggunaRequest;
use App\Http\Requests\Baak\UbahPenggunaRequest;
use App\Models\Pengguna;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

// ==========================================
// PEMBAHASAN / BAGIAN: CRUD Akun Kampus oleh Petugas BAAK
// ==========================================
class PenggunaController extends Controller
{
    // ==========================================
    // PEMBAHASAN / BAGIAN: Daftar Pengguna dan Profil
    // ==========================================
    public function index(): View
    {
        $pengguna = Pengguna::query()
            ->with(['mahasiswa', 'dosen'])
            ->orderBy('NAMALENGKAP')
            ->paginate(15);

        return view('baak.pengguna.indeks', compact('pengguna'));
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Form Akun dan Profil Baru
    // ==========================================
    public function create(): View
    {
        return view('baak.pengguna.form', ['akun' => null]);
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Menyimpan Akun dan Profil Baru
    // ==========================================
    public function store(SimpanPenggunaRequest $request): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data): void {
            $pengguna = Pengguna::create([
                'USERNAME' => $data['USERNAME'],
                'PASSWORD' => $data['PASSWORD'],
                'NAMALENGKAP' => $data['NAMALENGKAP'],
                'EMAIL' => $data['EMAIL'] ?? null,
                'NOMORTELEPON' => $data['NOMORTELEPON'] ?? null,
                'ADALAHBAAK' => $data['JENISPERAN'] === 'BAAK',
                'STATUSAKUN' => $data['STATUSAKUN'],
            ]);

            if ($data['JENISPERAN'] === 'MAHASISWA') {
                $pengguna->mahasiswa()->create([
                    'NIM' => $data['NIM'],
                    'NAMAMAHASISWA' => $data['NAMALENGKAP'],
                    'PROGRAMSTUDI' => $data['PROGRAMSTUDI'],
                    'ANGKATAN' => $data['ANGKATAN'],
                ]);
            }

            if (in_array($data['JENISPERAN'], ['DOSEN', 'PIMPINAN'], true)) {
                $pengguna->dosen()->create([
                    'NIDN' => $data['NIDN'],
                    'NIP' => $data['NIP'] ?? null,
                    'NAMADOSEN' => $data['NAMALENGKAP'],
                    'PROGRAMSTUDI' => $data['PROGRAMSTUDI'],
                    'JABATAN' => $data['JABATAN'] ?? null,
                    'LEVELPERSETUJUAN' => $data['LEVELPERSETUJUAN'] ?? null,
                ]);
            }
        });

        return to_route('baak.pengguna.index')->with('status', 'Akun kampus berhasil dibuat.');
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Form Perubahan Akun dan Profil
    // ==========================================
    public function edit(Pengguna $pengguna): View
    {
        return view('baak.pengguna.form', ['akun' => $pengguna->load(['mahasiswa', 'dosen'])]);
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Memperbarui Identitas Akun dan Profil
    // ==========================================
    public function update(UbahPenggunaRequest $request, Pengguna $pengguna): RedirectResponse
    {
        $data = $request->validated();
        $perubahanAkun = [
            'USERNAME' => $data['USERNAME'],
            'NAMALENGKAP' => $data['NAMALENGKAP'],
            'EMAIL' => $data['EMAIL'] ?? null,
            'NOMORTELEPON' => $data['NOMORTELEPON'] ?? null,
            'STATUSAKUN' => $data['STATUSAKUN'],
        ];

        if (! empty($data['PASSWORD'])) {
            $perubahanAkun['PASSWORD'] = $data['PASSWORD'];
        }

        DB::transaction(function () use ($pengguna, $data, $perubahanAkun): void {
            $pengguna->update($perubahanAkun);

            if ($pengguna->mahasiswa) {
                $pengguna->mahasiswa->update([
                    'NIM' => $data['NIM'],
                    'NAMAMAHASISWA' => $data['NAMALENGKAP'],
                    'PROGRAMSTUDI' => $data['PROGRAMSTUDI'],
                    'ANGKATAN' => $data['ANGKATAN'],
                ]);
            }

            if ($pengguna->dosen) {
                $pengguna->dosen->update([
                    'NIDN' => $data['NIDN'],
                    'NIP' => $data['NIP'] ?? null,
                    'NAMADOSEN' => $data['NAMALENGKAP'],
                    'PROGRAMSTUDI' => $data['PROGRAMSTUDI'],
                    'JABATAN' => $data['JABATAN'] ?? null,
                    'LEVELPERSETUJUAN' => $data['LEVELPERSETUJUAN'] ?? null,
                ]);
            }
        });

        return to_route('baak.pengguna.index')->with('status', 'Data akun berhasil diperbarui.');
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Menonaktifkan Akun tanpa Menghapus Riwayat Akademik
    // ==========================================
    public function destroy(Pengguna $pengguna): RedirectResponse
    {
        abort_if($pengguna->is(auth()->user()), 403, 'Akun BAAK yang sedang digunakan tidak dapat dinonaktifkan.');

        $pengguna->update(['STATUSAKUN' => 'NONAKTIF']);

        return to_route('baak.pengguna.index')->with('status', 'Akun dinonaktifkan; riwayat akademik tetap dipertahankan.');
    }
}
