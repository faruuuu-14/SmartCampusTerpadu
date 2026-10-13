<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dosen\TinjauPresensiRequest;
use App\Models\Dosen;
use App\Models\Jadwal;
use App\Models\Pengguna;
use App\Models\Pertemuan;
use App\Models\Presensi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

// ==========================================
// PEMBAHASAN / BAGIAN: Pengelolaan Sesi Pertemuan oleh Dosen
// ==========================================
class PertemuanController extends Controller
{
    public function indeks(): View
    {
        /** @var Pengguna $pengguna */
        $pengguna = Auth::user();
        $dosen = $pengguna->dosen;

        abort_unless($dosen instanceof Dosen, 403);

        $jadwal = $dosen->jadwal()
            ->with([
                'krs.mahasiswa.pengguna',
                'pertemuan' => fn ($query) => $query->with('presensi')->orderByDesc('PERTEMUANKE'),
            ])
            ->orderBy('HARI')
            ->orderBy('JAMMULAI')
            ->get();
        $waktuKedaluwarsa = now()->addMinutes(5);
        $tautanPresensi = [];

        foreach ($jadwal as $mataKuliah) {
            foreach ($mataKuliah->pertemuan as $pertemuan) {
                if (
                    $pertemuan->STATUSPERTEMUAN !== 'BERLANGSUNG'
                    || $pertemuan->TUTUPPRESENSI === null
                    || now()->gt($pertemuan->TUTUPPRESENSI)
                ) {
                    continue;
                }

                foreach ($mataKuliah->krs as $krs) {
                    $mahasiswa = $krs->mahasiswa;

                    if ($pertemuan->presensi->contains('IDMAHASISWA', $mahasiswa->IDMAHASISWA)) {
                        continue;
                    }

                    $tautanPresensi[$pertemuan->IDPERTEMUAN][$mahasiswa->IDMAHASISWA] = [
                        'mahasiswa' => $mahasiswa,
                        'url' => URL::temporarySignedRoute(
                            'mahasiswa.presensi.tampilkan',
                            $waktuKedaluwarsa,
                            ['mahasiswa' => $mahasiswa, 'pertemuan' => $pertemuan],
                        ),
                    ];
                }
            }
        }

        return view('dosen.pertemuan', [
            'jadwal' => $jadwal,
            'tautanPresensi' => $tautanPresensi,
            'durasiPresensi' => config('presensi.durasi_menit'),
        ]);
    }

    public function buka(Jadwal $jadwal): RedirectResponse
    {
        /** @var Pengguna $pengguna */
        $pengguna = Auth::user();
        $dosen = $pengguna->dosen;

        abort_unless($dosen instanceof Dosen, 403);

        DB::transaction(function () use ($jadwal, $dosen): void {
            $jadwalTerkunci = Jadwal::query()
                ->whereKey($jadwal->IDJADWAL)
                ->lockForUpdate()
                ->firstOrFail();
            abort_unless((int) $jadwalTerkunci->IDDOSEN === (int) $dosen->IDDOSEN, 403);

            $adaSesiAktif = Pertemuan::query()
                ->where('IDJADWAL', $jadwalTerkunci->IDJADWAL)
                ->where('STATUSPERTEMUAN', 'BERLANGSUNG')
                ->exists();

            if ($adaSesiAktif) {
                throw ValidationException::withMessages([
                    'pertemuan' => 'Masih ada sesi yang berlangsung untuk jadwal ini.',
                ]);
            }

            $pertemuanKe = ((int) $jadwalTerkunci->pertemuan()->max('PERTEMUANKE')) + 1;
            $dibuka = now();

            $jadwalTerkunci->pertemuan()->create([
                'PERTEMUANKE' => $pertemuanKe,
                'TANGGALPERTEMUAN' => $dibuka->toDateString(),
                'STATUSPERTEMUAN' => 'BERLANGSUNG',
                'MODEKEHADIRAN' => $jadwalTerkunci->MODEKEHADIRAN,
                'BUKAPRESENSI' => $dibuka,
                'TUTUPPRESENSI' => $dibuka->copy()->addMinutes((int) config('presensi.durasi_menit')),
            ]);
        });

        return back()->with('status', 'Sesi presensi berhasil dibuka.');
    }

    public function tutup(Pertemuan $pertemuan): RedirectResponse
    {
        /** @var Pengguna $pengguna */
        $pengguna = Auth::user();
        $dosen = $pengguna->dosen;

        abort_unless($dosen instanceof Dosen, 403);
        abort_unless($pertemuan->jadwal()->where('IDDOSEN', $dosen->IDDOSEN)->exists(), 403);

        if ($pertemuan->STATUSPERTEMUAN !== 'BERLANGSUNG') {
            throw ValidationException::withMessages([
                'pertemuan' => 'Sesi ini tidak sedang berlangsung.',
            ]);
        }

        $pertemuan->forceFill([
            'STATUSPERTEMUAN' => 'SELESAI',
            'TUTUPPRESENSI' => now(),
        ])->save();

        return back()->with('status', 'Sesi presensi berhasil ditutup.');
    }

    public function tinjau(TinjauPresensiRequest $request, Presensi $presensi): RedirectResponse
    {
        /** @var Pengguna $pengguna */
        $pengguna = Auth::user();
        $dosen = $pengguna->dosen;

        abort_unless($dosen instanceof Dosen, 403);
        abort_unless($presensi->pertemuan()->whereHas('jadwal', fn ($query) => $query->where('IDDOSEN', $dosen->IDDOSEN))->exists(), 403);

        DB::transaction(function () use ($request, $presensi, $pengguna): void {
            $presensiTerkunci = Presensi::query()
                ->whereKey($presensi->IDPRESENSI)
                ->lockForUpdate()
                ->firstOrFail();

            if ($presensiTerkunci->STATUSPRESENSI !== 'PERLUREVIEW') {
                throw ValidationException::withMessages([
                    'presensi' => 'Hanya presensi yang menunggu peninjauan yang dapat diputuskan.',
                ]);
            }

            $presensiTerkunci->forceFill([
                'STATUSPRESENSI' => $request->validated('KEPUTUSAN'),
                'IDPENINJAU' => $pengguna->IDPENGGUNA,
            ])->save();
        });

        return back()->with('status', 'Keputusan peninjauan presensi berhasil disimpan.');
    }
}
