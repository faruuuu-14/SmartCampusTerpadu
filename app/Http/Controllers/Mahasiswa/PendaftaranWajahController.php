<?php

// ==========================================
// PEMBAHASAN / BAGIAN: Persetujuan dan Pendaftaran Wajah Mahasiswa
// ==========================================

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Layanan\Presensi\PendaftaranWajahLayanan;
use App\Models\Mahasiswa;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PendaftaranWajahController extends Controller
{
    public function indeks(Request $request): View
    {
        $mahasiswa = Mahasiswa::query()
            ->where('IDPENGGUNA', $request->user()->IDPENGGUNA)
            ->firstOrFail();

        return view('mahasiswa.wajah', compact('mahasiswa'));
    }

    public function daftarkan(
        Request $request,
        PendaftaranWajahLayanan $pendaftaran,
    ): RedirectResponse {
        $data = $request->validate([
            'PERSETUJUAN' => ['required', 'accepted'],
            'foto' => ['required', 'image', 'mimes:jpg,jpeg', 'max:10240'],
        ]);
        $mahasiswa = Mahasiswa::query()
            ->where('IDPENGGUNA', $request->user()->IDPENGGUNA)
            ->firstOrFail();

        try {
            $pendaftaran->daftarkan($mahasiswa, $data['foto'], true);
        } catch (ValidationException $kesalahan) {
            throw $kesalahan;
        } catch (ConnectionException|\Illuminate\Http\Client\RequestException|\LogicException $kesalahan) {
            Log::warning('Pendaftaran wajah gagal menghubungi layanan biometrik.', [
                'alasan' => $kesalahan::class,
            ]);

            return back()->withErrors([
                'foto' => 'Layanan verifikasi wajah sedang tidak tersedia. Tidak ada foto yang disimpan; coba kembali nanti.',
            ]);
        }

        return redirect()->route('mahasiswa.wajah.indeks')
            ->with('status', 'Persetujuan tercatat dan pendaftaran menunggu verifikasi BAAK.');
    }

    public function cabut(Request $request, PendaftaranWajahLayanan $pendaftaran): RedirectResponse
    {
        $mahasiswa = Mahasiswa::query()
            ->where('IDPENGGUNA', $request->user()->IDPENGGUNA)
            ->firstOrFail();
        $pendaftaran->cabut($mahasiswa);

        return redirect()->route('mahasiswa.wajah.indeks')
            ->with('status', 'Persetujuan dicabut; foto dan embedding referensi telah dihapus.');
    }
}
