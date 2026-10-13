<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Http\Requests\Mahasiswa\KumpulkanTugasRequest;
use App\Models\Pengguna;
use App\Models\Pengumpulan;
use App\Models\Tugas;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

// ==========================================
// PEMBAHASAN / BAGIAN: Daftar Tugas Mahasiswa dan CRUD Pengumpulan
// ==========================================
class TugasController extends Controller
{
    // ==========================================
    // PEMBAHASAN / BAGIAN: Daftar Tugas dari Kelas yang Diambil
    // ==========================================
    public function indeks(): View
    {
        $pengguna = auth()->user();
        abort_unless($pengguna instanceof Pengguna && $pengguna->mahasiswa, 403);
        $idMahasiswa = $pengguna->mahasiswa->IDMAHASISWA;

        $tugas = Tugas::query()
            ->with(['jadwal', 'dosen', 'pengumpulan' => fn ($query) => $query->where('IDMAHASISWA', $idMahasiswa)])
            ->whereHas('jadwal.krs', fn ($query) => $query->where('IDMAHASISWA', $idMahasiswa))
            ->orderBy('TANGGALTENGGAT')
            ->paginate(15);

        return view('mahasiswa.tugas', compact('tugas'));
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Mengirim atau Memperbarui Pengumpulan
    // ==========================================
    public function simpan(KumpulkanTugasRequest $request, Tugas $tugas): RedirectResponse
    {
        $pengguna = $request->user();
        abort_unless($pengguna instanceof Pengguna && $pengguna->mahasiswa, 403);
        $idMahasiswa = $pengguna->mahasiswa->IDMAHASISWA;
        $pengumpulan = $tugas->pengumpulan()->where('IDMAHASISWA', $idMahasiswa)->first();
        abort_if($pengumpulan?->STATUSPENGUMPULAN === 'DINILAI', 403, 'Pengumpulan yang telah dinilai tidak dapat diubah.');

        $berkas = $request->file('BERKAS');
        $berkasLama = $pengumpulan?->FILEKUMPUL;
        $fileBaru = $berkas->store('pengumpulan', 'local');
        $status = $tugas->TANGGALTENGGAT->isPast() ? 'TERLAMBAT' : 'SUDAH';

        $pengumpulan ??= new Pengumpulan([
            'IDTUGAS' => $tugas->IDTUGAS,
            'IDMAHASISWA' => $idMahasiswa,
        ]);
        $pengumpulan->fill([
            'STATUSPENGUMPULAN' => $status,
            'FILEKUMPUL' => $fileBaru,
            'HASHFILE' => hash_file('sha256', $berkas->getRealPath()),
            'WAKTUKUMPUL' => now(),
        ])->save();

        if ($berkasLama) {
            Storage::disk('local')->delete($berkasLama);
        }

        return to_route('mahasiswa.tugas.indeks')->with('status', 'Pengumpulan tugas berhasil disimpan.');
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Menghapus Pengumpulan Sebelum Penilaian
    // ==========================================
    public function hapus(Tugas $tugas): RedirectResponse
    {
        $pengguna = auth()->user();
        abort_unless($pengguna instanceof Pengguna && session('PERANAKTIF') === 'MAHASISWA' && $pengguna->mahasiswa, 403);

        $pengumpulan = $tugas->pengumpulan()
            ->where('IDMAHASISWA', $pengguna->mahasiswa->IDMAHASISWA)
            ->firstOrFail();
        abort_if($pengumpulan->STATUSPENGUMPULAN === 'DINILAI', 403, 'Pengumpulan yang telah dinilai tidak dapat dihapus.');

        if ($pengumpulan->FILEKUMPUL) {
            Storage::disk('local')->delete($pengumpulan->FILEKUMPUL);
        }
        $pengumpulan->delete();

        return to_route('mahasiswa.tugas.indeks')->with('status', 'Pengumpulan tugas berhasil dihapus.');
    }
}
