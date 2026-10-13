<?php

namespace App\Layanan;

use App\Layanan\TandaTanganElektronik\KunciLayanan;
use App\Layanan\TandaTanganElektronik\PembekuIsiSurat;
use App\Layanan\TandaTanganElektronik\PenandaTangan;
use App\Layanan\TandaTanganElektronik\PenerbitanSuratLayanan;
use App\Models\Administrasi;
use App\Models\Dosen;
use App\Models\Pengguna;
use App\Models\PersetujuanSurat;
use App\Models\RiwayatTindakanSurat;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

// ==========================================
// PEMBAHASAN / BAGIAN: Orkestrasi Persetujuan Berantai Surat
// ==========================================
class AlurPersetujuanSurat
{
    // ==========================================
    // PEMBAHASAN / BAGIAN: Ketergantungan Alur Keputusan Surat
    // ==========================================
    public function __construct(
        private PembekuIsiSurat $pembekuIsiSurat,
        private KunciLayanan $kunciLayanan,
        private PenandaTangan $penandaTangan,
        private PenerbitanSuratLayanan $penerbitanSuratLayanan,
    ) {}

    // ==========================================
    // PEMBAHASAN / BAGIAN: Lolos Verifikasi BAAK dan Membentuk Antrean
    // ==========================================
    public function lolosBaak(Administrasi $surat, Pengguna $pelaku, array $ceklist): void
    {
        DB::transaction(function () use ($surat, $pelaku, $ceklist): void {
            $terkunci = Administrasi::query()->whereKey($surat->getKey())->lockForUpdate()->firstOrFail();

            if (! in_array($terkunci->STATUSADMINISTRASI, ['DIAJUKAN', 'PERLUREVISI'], true)) {
                throw new ConflictHttpException('Surat ini tidak sedang menunggu verifikasi BAAK.');
            }

            $pimpinan = Dosen::query()
                ->whereNotNull('JABATAN')
                ->whereNotNull('LEVELPERSETUJUAN')
                ->where('JABATAN', '<>', '')
                ->with('pengguna')
                ->orderBy('LEVELPERSETUJUAN')
                ->orderBy('IDDOSEN')
                ->take(2)
                ->get();

            if ($pimpinan->count() !== 2 || $pimpinan->contains(fn (Dosen $dosen): bool => $dosen->pengguna === null)) {
                throw new RuntimeException('Dua pimpinan berjabatan wajib tersedia sebelum surat diloloskan.');
            }

            foreach ($pimpinan as $dosen) {
                $this->kunciLayanan->buatJikaBelumAda($dosen->pengguna);
            }

            $this->pembekuIsiSurat->bekukan($terkunci);
            $terkunci->refresh();
            $terkunci->persetujuanSurat()->delete();

            foreach ($pimpinan as $urutan => $dosen) {
                $terkunci->persetujuanSurat()->create([
                    'IDPENYETUJU' => $dosen->IDPENGGUNA,
                    'PERANPENYETUJU' => 'PIMPINAN',
                    'URUTAN' => $urutan + 1,
                    'STATUSPERSETUJUAN' => 'MENUNGGU',
                ]);
            }

            $this->catat($terkunci, $pelaku, 'BAAK_LOLOS', 'Ceklist: '.implode(', ', $ceklist));
        });
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Meminta Revisi dengan Catatan
    // ==========================================
    public function mintaRevisi(Administrasi $surat, Pengguna $pelaku, string $catatan): void
    {
        DB::transaction(function () use ($surat, $pelaku, $catatan): void {
            $terkunci = Administrasi::query()->whereKey($surat->getKey())->lockForUpdate()->firstOrFail();

            if (! in_array($terkunci->STATUSADMINISTRASI, ['DIAJUKAN', 'PERLUREVISI'], true)) {
                throw new ConflictHttpException('Surat ini tidak dapat diminta revisi.');
            }

            $terkunci->forceFill([
                'STATUSADMINISTRASI' => 'PERLUREVISI',
                'CATATAN' => $catatan,
            ])->save();
            $this->catat($terkunci, $pelaku, 'BAAK_REVISI', $catatan);
        });
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Autentikasi Ulang dan Tanda Tangan Pimpinan
    // ==========================================
    public function setujui(PersetujuanSurat $persetujuan, Pengguna $pelaku, string $kataSandi): void
    {
        DB::transaction(function () use ($persetujuan, $pelaku, $kataSandi): void {
            $terkunci = PersetujuanSurat::query()
                ->whereKey($persetujuan->getKey())
                ->lockForUpdate()
                ->with('administrasi')
                ->firstOrFail();
            $surat = $terkunci->administrasi;

            $this->pastikanGiliranAktif($terkunci, $pelaku);
            if (! Hash::check($kataSandi, $pelaku->PASSWORD)) {
                throw ValidationException::withMessages(['PASSWORD' => 'Kata sandi tidak sesuai.']);
            }

            $this->penandaTangan->tandatangani($terkunci);
            $this->catat($surat, $pelaku, 'TTE_SETUJU', 'Persetujuan elektronik urutan '.$terkunci->URUTAN.'.');

            $belumDisetujui = $surat->persetujuanSurat()
                ->where('STATUSPERSETUJUAN', '<>', 'DISETUJUI')
                ->exists();
            if (! $belumDisetujui) {
                $this->penerbitanSuratLayanan->terbitkan($surat);
            }
        });
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Penolakan Surat oleh Pimpinan
    // ==========================================
    public function tolak(PersetujuanSurat $persetujuan, Pengguna $pelaku, string $catatan): void
    {
        DB::transaction(function () use ($persetujuan, $pelaku, $catatan): void {
            $terkunci = PersetujuanSurat::query()
                ->whereKey($persetujuan->getKey())
                ->lockForUpdate()
                ->with('administrasi')
                ->firstOrFail();
            $surat = $terkunci->administrasi;

            $this->pastikanGiliranAktif($terkunci, $pelaku);
            $terkunci->forceFill([
                'STATUSPERSETUJUAN' => 'DITOLAK',
                'CATATAN' => $catatan,
                'WAKTUKEPUTUSAN' => now(),
            ])->save();
            $surat->forceFill([
                'STATUSADMINISTRASI' => 'DITOLAK',
                'CATATAN' => $catatan,
            ])->save();
            $this->catat($surat, $pelaku, 'TTE_TOLAK', $catatan);
        });
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Memastikan Identitas dan Urutan Penyetuju
    // ==========================================
    private function pastikanGiliranAktif(PersetujuanSurat $persetujuan, Pengguna $pelaku): void
    {
        $surat = $persetujuan->administrasi;
        $adaSebelumnya = $surat->persetujuanSurat()
            ->where('URUTAN', '<', $persetujuan->URUTAN)
            ->where('STATUSPERSETUJUAN', '<>', 'DISETUJUI')
            ->exists();

        if ($persetujuan->IDPENYETUJU !== $pelaku->IDPENGGUNA
            || $persetujuan->STATUSPERSETUJUAN !== 'MENUNGGU'
            || $surat->STATUSADMINISTRASI !== 'MENUNGGUPERSETUJUAN'
            || $adaSebelumnya) {
            throw new AccessDeniedHttpException('Persetujuan ini belum menjadi giliran Anda.');
        }
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Merekam Jejak Audit Tindakan
    // ==========================================
    private function catat(Administrasi $surat, Pengguna $pelaku, string $tindakan, ?string $catatan): void
    {
        RiwayatTindakanSurat::query()->create([
            'IDADMINISTRASI' => $surat->IDADMINISTRASI,
            'IDPELAKU' => $pelaku->IDPENGGUNA,
            'TINDAKAN' => $tindakan,
            'CATATAN' => $catatan,
            'WAKTU' => now(),
        ]);
    }
}
