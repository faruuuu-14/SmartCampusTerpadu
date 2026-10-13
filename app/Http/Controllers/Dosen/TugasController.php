<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dosen\BuatTugasRequest;
use App\Http\Requests\Dosen\NilaiPengumpulanRequest;
use App\Models\Dosen;
use App\Models\Pengguna;
use App\Models\Pengumpulan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

// ==========================================
// PEMBAHASAN / BAGIAN: Pengelolaan Tugas dan Hasil Pengumpulan Dosen
// ==========================================
class TugasController extends Controller
{
    // ==========================================
    // PEMBAHASAN / BAGIAN: Menampilkan Tugas, Pengumpulan, dan Form Tugas Baru
    // ==========================================
    public function indeks(): View
    {
        $pengguna = auth()->user();
        abort_unless($pengguna instanceof Pengguna && $pengguna->dosen instanceof Dosen, 403);
        $jadwal = $pengguna->dosen->jadwal()
            ->with(['tugas.pengumpulan.mahasiswa.pengguna'])
            ->orderBy('HARI')
            ->orderBy('JAMMULAI')
            ->get();

        return view('dosen.tugas', compact('jadwal'));
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Membuat Tugas untuk Jadwal Milik Dosen
    // ==========================================
    public function simpan(BuatTugasRequest $request): RedirectResponse
    {
        $pengguna = $request->user();
        abort_unless($pengguna instanceof Pengguna && $pengguna->dosen instanceof Dosen, 403);
        $data = $request->validated();

        $pengguna->dosen->tugas()->create([
            'IDJADWAL' => $data['IDJADWAL'],
            'JUDULTUGAS' => $data['JUDULTUGAS'],
            'DESKRIPSI' => $data['DESKRIPSI'] ?? null,
            'TANGGALTENGGAT' => $data['TANGGALTENGGAT'],
            'MODEPENGERJAAN' => $data['MODEPENGERJAAN'],
            'DIBUATVIA' => 'WEB',
            'TANGGALSIARAN' => now(),
        ]);

        return to_route('dosen.tugas.indeks')->with('status', 'Tugas berhasil dibuat.');
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Mengunduh Berkas Pengumpulan secara Privat
    // ==========================================
    public function unduh(Pengumpulan $pengumpulan): StreamedResponse
    {
        $pengguna = auth()->user();
        abort_unless($pengguna instanceof Pengguna && $pengguna->dosen instanceof Dosen, 403);
        abort_unless($pengumpulan->tugas()->where('IDDOSEN', $pengguna->dosen->IDDOSEN)->exists(), 403);

        if (! $pengumpulan->FILEKUMPUL || ! Storage::disk('local')->exists($pengumpulan->FILEKUMPUL)) {
            throw new NotFoundHttpException('Berkas pengumpulan tidak ditemukan.');
        }

        return Storage::disk('local')->download($pengumpulan->FILEKUMPUL);
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Memberi Nilai dan Umpan Balik Pengumpulan
    // ==========================================
    public function nilai(NilaiPengumpulanRequest $request, Pengumpulan $pengumpulan): RedirectResponse
    {
        $pengumpulan->forceFill([
            'NILAI' => $request->validated('NILAI'),
            'UMPANBALIK' => $request->validated('UMPANBALIK'),
            'STATUSPENGUMPULAN' => 'DINILAI',
        ])->save();

        return to_route('dosen.tugas.indeks')->with('status', 'Nilai dan umpan balik berhasil disimpan.');
    }
}
