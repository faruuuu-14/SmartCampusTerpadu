<?php

namespace App\Http\Controllers\Baak;

use App\Http\Controllers\Controller;
use App\Http\Requests\Baak\SimpanJadwalRequest;
use App\Models\Dosen;
use App\Models\Jadwal;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\HttpException;

// ==========================================
// PEMBAHASAN / BAGIAN: CRUD Jadwal Perkuliahan BAAK
// ==========================================
class JadwalController extends Controller
{
    // ==========================================
    // PEMBAHASAN / BAGIAN: Daftar Jadwal Kuliah dan Pengampu
    // ==========================================
    public function index(): View
    {
        $jadwal = Jadwal::query()->with('dosen')->orderBy('HARI')->orderBy('JAMMULAI')->paginate(20);

        return view('baak.jadwal.indeks', compact('jadwal'));
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Form Jadwal Baru
    // ==========================================
    public function create(): View
    {
        return view('baak.jadwal.form', ['jadwal' => null, 'dosen' => Dosen::query()->orderBy('NAMADOSEN')->get()]);
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Membuat Jadwal Perkuliahan
    // ==========================================
    public function store(SimpanJadwalRequest $request): RedirectResponse
    {
        Jadwal::query()->create($request->validated());

        return to_route('baak.jadwal.index')->with('status', 'Jadwal perkuliahan berhasil dibuat.');
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Form Perubahan Jadwal
    // ==========================================
    public function edit(Jadwal $jadwal): View
    {
        return view('baak.jadwal.form', [
            'jadwal' => $jadwal,
            'dosen' => Dosen::query()->orderBy('NAMADOSEN')->get(),
        ]);
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Memperbarui Jadwal tanpa Mengubah Riwayat Pertemuan
    // ==========================================
    public function update(SimpanJadwalRequest $request, Jadwal $jadwal): RedirectResponse
    {
        if ($jadwal->pertemuan()->exists()) {
            throw new HttpException(409, 'Jadwal dengan riwayat pertemuan tidak dapat diubah.');
        }

        $jadwal->update($request->validated());

        return to_route('baak.jadwal.index')->with('status', 'Jadwal perkuliahan berhasil diperbarui.');
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Menghapus Jadwal tanpa Riwayat Turunan
    // ==========================================
    public function destroy(Jadwal $jadwal): RedirectResponse
    {
        abort_if(
            $jadwal->krs()->exists() || $jadwal->pertemuan()->exists() || $jadwal->tugas()->exists(),
            409,
            'Jadwal yang sudah dipakai KRS, pertemuan, atau tugas tidak dapat dihapus.'
        );

        $jadwal->delete();

        return to_route('baak.jadwal.index')->with('status', 'Jadwal perkuliahan berhasil dihapus.');
    }
}
