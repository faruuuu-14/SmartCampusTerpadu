<?php

namespace App\Http\Controllers\Baak;

use App\Http\Controllers\Controller;
use App\Http\Requests\Baak\SimpanKrsRequest;
use App\Models\Jadwal;
use App\Models\Krs;
use App\Models\Mahasiswa;
use App\Models\Pengumpulan;
use App\Models\Presensi;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\HttpException;

// ==========================================
// PEMBAHASAN / BAGIAN: CRUD Enrolmen KRS oleh BAAK
// ==========================================
class KrsController extends Controller
{
    // ==========================================
    // PEMBAHASAN / BAGIAN: Daftar KRS dan Pilihan Data Induk
    // ==========================================
    public function index(): View
    {
        $krs = Krs::query()
            ->with(['jadwal.dosen', 'mahasiswa.pengguna'])
            ->orderByDesc('IDKRS')
            ->paginate(20);

        return view('baak.krs.indeks', [
            'krs' => $krs,
            'jadwal' => Jadwal::query()->orderBy('NAMAMATAKULIAH')->get(),
            'mahasiswa' => Mahasiswa::query()->with('pengguna')->orderBy('NIM')->get(),
        ]);
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Menambahkan Mahasiswa ke Kelas
    // ==========================================
    public function store(SimpanKrsRequest $request): RedirectResponse
    {
        Krs::query()->create($request->validated());

        return to_route('baak.krs.index')->with('status', 'Mahasiswa berhasil ditambahkan ke kelas.');
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Mengubah Enrolmen tanpa Menghilangkan Riwayat
    // ==========================================
    public function update(SimpanKrsRequest $request, Krs $krs): RedirectResponse
    {
        $this->pastikanTanpaRiwayat($krs);
        $krs->update($request->validated());

        return to_route('baak.krs.index')->with('status', 'Enrolmen KRS berhasil diperbarui.');
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Menghapus Enrolmen tanpa Menghapus Presensi/Tugas
    // ==========================================
    public function destroy(Krs $krs): RedirectResponse
    {
        $this->pastikanTanpaRiwayat($krs);
        $krs->delete();

        return to_route('baak.krs.index')->with('status', 'Enrolmen KRS berhasil dihapus.');
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Menjaga Riwayat Akademik Tetap Terhubung ke KRS
    // ==========================================
    private function pastikanTanpaRiwayat(Krs $krs): void
    {
        $adaPresensi = Presensi::query()
            ->where('IDMAHASISWA', $krs->IDMAHASISWA)
            ->whereHas('pertemuan', fn ($query) => $query->where('IDJADWAL', $krs->IDJADWAL))
            ->exists();
        $adaPengumpulan = Pengumpulan::query()
            ->where('IDMAHASISWA', $krs->IDMAHASISWA)
            ->whereHas('tugas', fn ($query) => $query->where('IDJADWAL', $krs->IDJADWAL))
            ->exists();

        if ($adaPresensi || $adaPengumpulan) {
            throw new HttpException(409, 'KRS yang memiliki riwayat presensi atau pengumpulan tugas tidak dapat diubah/hapus.');
        }
    }
}
