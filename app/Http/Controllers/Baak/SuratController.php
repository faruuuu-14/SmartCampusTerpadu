<?php

namespace App\Http\Controllers\Baak;

use App\Http\Controllers\Controller;
use App\Http\Requests\Baak\TinjauSuratRequest;
use App\Layanan\AlurPersetujuanSurat;
use App\Models\Administrasi;
use App\Models\Pengguna;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

// ==========================================
// PEMBAHASAN / BAGIAN: Verifikasi Administrasi Surat oleh BAAK
// ==========================================
class SuratController extends Controller
{
    // ==========================================
    // PEMBAHASAN / BAGIAN: Menampilkan Surat Masuk untuk Verifikasi
    // ==========================================
    public function indeks(): View
    {
        $suratMasuk = Administrasi::query()
            ->with('pemohon.mahasiswa')
            ->whereIn('STATUSADMINISTRASI', ['DIAJUKAN', 'PERLUREVISI'])
            ->orderBy('TANGGALBUAT')
            ->get();

        return view('baak.surat', compact('suratMasuk'));
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Memeriksa Lampiran Secara Privat
    // ==========================================
    public function lampiran(Administrasi $administrasi): StreamedResponse
    {
        if (session('PERANAKTIF') !== 'BAAK') {
            throw new AccessDeniedHttpException;
        }

        if (! $administrasi->FILELAMPIRAN || ! Storage::disk('local')->exists($administrasi->FILELAMPIRAN)) {
            throw new NotFoundHttpException('Lampiran surat tidak ditemukan.');
        }

        return Storage::disk('local')->download($administrasi->FILELAMPIRAN);
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Memproses Persetujuan Lanjut atau Permintaan Revisi
    // ==========================================
    public function tinjau(
        TinjauSuratRequest $request,
        Administrasi $administrasi,
        AlurPersetujuanSurat $alur
    ): RedirectResponse {
        $pelaku = $request->user();
        abort_unless($pelaku instanceof Pengguna, 403);

        if ($request->validated('KEPUTUSAN') === 'LANJUT') {
            $ceklist = $request->validated('CEKLIST', []);
            if (count(array_unique($ceklist)) !== 3
                || ! collect(['IDENTITAS', 'KEPERLUAN', 'LAMPIRAN'])->every(fn (string $item): bool => in_array($item, $ceklist, true))) {
                return back()->withErrors(['CEKLIST' => 'Seluruh butir verifikasi wajib dicentang untuk meloloskan surat.']);
            }

            $alur->lolosBaak($administrasi, $pelaku, $ceklist);
        } else {
            $alur->mintaRevisi($administrasi, $pelaku, $request->validated('CATATAN'));
        }

        return to_route('baak.surat.indeks')->with('status', 'Tindakan verifikasi surat berhasil dicatat.');
    }
}
