<?php

// ==========================================
// PEMBAHASAN / BAGIAN: Verifikasi Pendaftaran Biometrik oleh BAAK
// ==========================================

namespace App\Http\Controllers\Baak;

use App\Http\Controllers\Controller;
use App\Models\Mahasiswa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class VerifikasiWajahController extends Controller
{
    public function indeks(): View
    {
        $mahasiswa = Mahasiswa::query()
            ->with('pengguna')
            ->where('STATUSWAJAH', 'MENUNGGU_VERIFIKASI')
            ->orderBy('IDMAHASISWA')
            ->paginate(20);

        return view('baak.verifikasi-wajah', compact('mahasiswa'));
    }

    public function simpan(Request $request, Mahasiswa $mahasiswa): RedirectResponse
    {
        $data = $request->validate([
            'STATUSWAJAH' => ['required', 'in:AKTIF,DITOLAK'],
        ]);

        abort_unless($mahasiswa->STATUSWAJAH === 'MENUNGGU_VERIFIKASI', 409);
        abort_unless(
            $mahasiswa->TANGGALKONSENBIOMETRIK !== null
                && is_string($mahasiswa->EMBEDDINGWAJAH)
                && is_string($mahasiswa->MODELWAJAH)
                && is_string($mahasiswa->FOTOREFERENSI)
                && Storage::disk('local')->exists($mahasiswa->FOTOREFERENSI),
            409,
            'Pendaftaran belum memiliki persetujuan dan embedding yang valid.',
        );

        $jalurFoto = $mahasiswa->FOTOREFERENSI;
        $mahasiswa->forceFill([
            'STATUSWAJAH' => $data['STATUSWAJAH'],
            'IDVERIFIKASIBIOMETRIK' => $request->user()->IDPENGGUNA,
            'WAKTUTINJAUBIOMETRIK' => now(),
        ]);

        if ($data['STATUSWAJAH'] === 'DITOLAK') {
            $mahasiswa->forceFill([
                'EMBEDDINGWAJAH' => null,
                'FOTOREFERENSI' => null,
                'MODELWAJAH' => null,
            ]);
        }

        $mahasiswa->save();

        if ($data['STATUSWAJAH'] === 'DITOLAK' && is_string($jalurFoto)) {
            if (! Storage::disk('local')->delete($jalurFoto)) {
                throw new RuntimeException('Pendaftaran ditolak, tetapi foto referensi gagal dihapus.');
            }
        }

        return back()->with('status', 'Keputusan verifikasi biometrik berhasil disimpan.');
    }

    public function foto(Mahasiswa $mahasiswa): BinaryFileResponse
    {
        abort_unless($mahasiswa->STATUSWAJAH === 'MENUNGGU_VERIFIKASI', 404);
        $jalurFoto = $mahasiswa->FOTOREFERENSI;
        abort_unless(is_string($jalurFoto) && Storage::disk('local')->exists($jalurFoto), 404);

        return response()->file(Storage::disk('local')->path($jalurFoto), [
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
