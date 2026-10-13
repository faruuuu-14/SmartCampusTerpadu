<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Http\Requests\Mahasiswa\KirimPresensiRequest;
use App\Layanan\PresensiLayanan;
use App\Models\Mahasiswa;
use App\Models\Pertemuan;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

// ==========================================
// PEMBAHASAN / BAGIAN: Halaman dan Kiriman Presensi Bertanda Tangan
// ==========================================
class PresensiController extends Controller
{
    public function tampilkan(Mahasiswa $mahasiswa, Pertemuan $pertemuan, PresensiLayanan $layanan): View
    {
        $tantangan = $layanan->buatTantangan($mahasiswa, $pertemuan);

        return view('mahasiswa.presensi', [
            'mahasiswa' => $mahasiswa->load('pengguna'),
            'pertemuan' => $pertemuan->load('jadwal'),
            'tantangan' => $tantangan,
        ]);
    }

    public function simpan(
        KirimPresensiRequest $request,
        Mahasiswa $mahasiswa,
        Pertemuan $pertemuan,
        PresensiLayanan $layanan,
    ): JsonResponse {
        $presensi = $layanan->catat($mahasiswa, $pertemuan, $request->validated());
        $pesan = match ($presensi->STATUSPRESENSI) {
            'HADIR' => 'Presensi berhasil dicatat sebagai hadir.',
            'PERLUREVIEW' => 'Presensi tercatat dan menunggu pemeriksaan dosen.',
            default => 'Presensi tercatat dengan status ditolak. Hubungi dosen untuk tindak lanjut.',
        };

        return response()->json([
            'pesan' => $pesan,
            'status' => $presensi->STATUSPRESENSI,
        ], 201);
    }
}
