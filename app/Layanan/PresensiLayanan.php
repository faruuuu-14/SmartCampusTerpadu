<?php

namespace App\Layanan;

use App\Layanan\Presensi\HasilPenilaianWajah;
use App\Layanan\Presensi\KeputusanWajah;
use App\Layanan\Presensi\PenilaiWajah;
use App\Layanan\Presensi\PenilaiWajahTerperinci;
use App\Models\Krs;
use App\Models\Mahasiswa;
use App\Models\Pertemuan;
use App\Models\Presensi;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

// ==========================================
// PEMBAHASAN / BAGIAN: Layanan Validasi dan Penyimpanan Presensi
// ==========================================
class PresensiLayanan
{
    public function __construct(private readonly PenilaiWajah $penilaiWajah) {}

    /**
     * @return array{token: string, instruksi: string}
     */
    public function buatTantangan(Mahasiswa $mahasiswa, Pertemuan $pertemuan): array
    {
        $pertemuan = $this->muatPertemuan($pertemuan);
        $this->pastikanSesiAktif($pertemuan);
        $this->pastikanTerdaftar($mahasiswa, $pertemuan);
        $this->pastikanBelumPresensi($mahasiswa, $pertemuan);

        $token = Str::random(48);
        $instruksi = [
            'Kedipkan mata satu kali.',
            'Senyum sebentar ke kamera.',
            'Putar kepala sedikit ke kiri, lalu kembali.',
        ];
        Cache::put($this->kunciTantangan($mahasiswa, $pertemuan), hash('sha256', $token), now()->addMinutes(5));

        return [
            'token' => $token,
            'instruksi' => $instruksi[random_int(0, count($instruksi) - 1)],
        ];
    }

    /**
     * @param  array{FOTOPRESENSI: UploadedFile, LINTANG: numeric-string|float, BUJUR: numeric-string|float, AKURASIGPS: numeric-string|float, TANTANGAN: string, LIVENESSLOLOS: string}  $data
     */
    public function catat(Mahasiswa $mahasiswa, Pertemuan $pertemuan, array $data): Presensi
    {
        $foto = $data['FOTOPRESENSI'];
        $pertemuan = $this->muatPertemuan($pertemuan);
        $jadwal = $pertemuan->jadwal;
        $this->pastikanSesiAktif($pertemuan);
        $this->pastikanTerdaftar($mahasiswa, $pertemuan);
        $this->pastikanBelumPresensi($mahasiswa, $pertemuan);
        $this->pastikanTantangan($mahasiswa, $pertemuan, $data['TANTANGAN']);

        if ($jadwal->LINTANG === null || $jadwal->BUJUR === null) {
            throw ValidationException::withMessages([
                'presensi' => 'Lokasi kelas belum dikonfigurasi. Hubungi dosen atau BAAK.',
            ]);
        }

        $lintang = (float) $data['LINTANG'];
        $bujur = (float) $data['BUJUR'];
        $akurasi = (float) $data['AKURASIGPS'];
        $jarak = $this->hitungJarakMeter($lintang, $bujur, (float) $jadwal->LINTANG, (float) $jadwal->BUJUR);
        $radius = max(1.0, (float) $jadwal->RADIUSMETER);
        $mode = $pertemuan->MODEKEHADIRAN ?: $jadwal->MODEKEHADIRAN;
        $radiusDiizinkan = $mode === 'BEBAS'
            ? $radius * (float) config('presensi.pengali_radius_bebas')
            : $radius;
        $hasilWajah = $this->penilaiWajah instanceof PenilaiWajahTerperinci
            ? $this->penilaiWajah->nilaiTerperinci($foto, $mahasiswa)
            : new HasilPenilaianWajah(
                $this->penilaiWajah->nilai($foto, $mahasiswa),
                1,
                null,
                0.0,
            );
        $skorWajah = $hasilWajah->skor;

        if ($skorWajah !== null && (! is_finite($skorWajah) || $skorWajah < -1 || $skorWajah > 1)) {
            throw new RuntimeException('Penilai wajah harus mengembalikan skor cosine antara -1 dan 1.');
        }

        $skorRisiko = $this->hitungSkorRisiko($jarak, $radiusDiizinkan, $akurasi, $skorWajah ?? 0.0);
        $ambangWajah = config('wajah.ambang');
        $statusWajah = (new KeputusanWajah)->statusAwal(
            $hasilWajah,
            is_numeric($ambangWajah) ? (float) $ambangWajah : null,
            (string) config('wajah.skor_di_bawah_ambang'),
        );
        $status = $statusWajah ?? $this->tentukanStatus(
            $jarak,
            $radiusDiizinkan,
            $akurasi,
            $skorWajah ?? 0.0,
            $skorRisiko,
        );
        $pathFoto = $foto->storeAs('presensi', Str::uuid().'.'.$foto->extension(), 'local');

        if ($pathFoto === false) {
            throw new RuntimeException('Foto presensi gagal disimpan.');
        }

        try {
            return DB::transaction(function () use ($mahasiswa, $pertemuan, $data, $lintang, $bujur, $akurasi, $jarak, $skorWajah, $hasilWajah, $skorRisiko, $status, $pathFoto): Presensi {
                $pertemuanTerkunci = Pertemuan::query()
                    ->whereKey($pertemuan->IDPERTEMUAN)
                    ->lockForUpdate()
                    ->firstOrFail();
                $this->pastikanSesiAktif($pertemuanTerkunci);
                $this->pastikanBelumPresensi($mahasiswa, $pertemuanTerkunci);

                $kunci = $this->kunciTantangan($mahasiswa, $pertemuanTerkunci);
                $tokenTersimpan = Cache::pull($kunci);

                if (! is_string($tokenTersimpan) || ! hash_equals($tokenTersimpan, hash('sha256', $data['TANTANGAN']))) {
                    throw ValidationException::withMessages([
                        'presensi' => 'Tantangan presensi sudah digunakan atau kedaluwarsa. Muat ulang halaman.',
                    ]);
                }

                return Presensi::query()->create([
                    'IDPERTEMUAN' => $pertemuanTerkunci->IDPERTEMUAN,
                    'IDMAHASISWA' => $mahasiswa->IDMAHASISWA,
                    'WAKTUKIRIM' => now(),
                    'FILEFOTO' => $pathFoto,
                    'LINTANG' => $lintang,
                    'BUJUR' => $bujur,
                    'AKURASIGPS' => $akurasi,
                    'JARAKMETER' => $jarak,
                    'SKORWAJAH' => $skorWajah,
                    'ALASANWAJAH' => $hasilWajah->alasan,
                    'LIVENESSLOLOS' => true,
                    'SKORRISIKO' => $skorRisiko,
                    'STATUSPRESENSI' => $status,
                ]);
            });
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($pathFoto);

            throw $exception;
        }
    }

    private function muatPertemuan(Pertemuan $pertemuan): Pertemuan
    {
        return Pertemuan::query()
            ->with('jadwal')
            ->whereKey($pertemuan->IDPERTEMUAN)
            ->firstOrFail();
    }

    private function pastikanSesiAktif(Pertemuan $pertemuan): void
    {
        if (
            $pertemuan->STATUSPERTEMUAN !== 'BERLANGSUNG'
            || $pertemuan->BUKAPRESENSI === null
            || $pertemuan->TUTUPPRESENSI === null
            || now()->lt($pertemuan->BUKAPRESENSI)
            || now()->gt($pertemuan->TUTUPPRESENSI)
        ) {
            throw ValidationException::withMessages([
                'presensi' => 'Sesi presensi belum dibuka atau sudah ditutup.',
            ]);
        }
    }

    private function pastikanTerdaftar(Mahasiswa $mahasiswa, Pertemuan $pertemuan): void
    {
        $terdaftar = Krs::query()
            ->where('IDMAHASISWA', $mahasiswa->IDMAHASISWA)
            ->where('IDJADWAL', $pertemuan->IDJADWAL)
            ->exists();

        if (! $terdaftar) {
            abort(403, 'Mahasiswa tidak terdaftar pada jadwal pertemuan ini.');
        }
    }

    private function pastikanBelumPresensi(Mahasiswa $mahasiswa, Pertemuan $pertemuan): void
    {
        if (Presensi::query()
            ->where('IDMAHASISWA', $mahasiswa->IDMAHASISWA)
            ->where('IDPERTEMUAN', $pertemuan->IDPERTEMUAN)
            ->exists()) {
            throw ValidationException::withMessages([
                'presensi' => 'Presensi untuk pertemuan ini sudah tercatat.',
            ]);
        }
    }

    private function pastikanTantangan(Mahasiswa $mahasiswa, Pertemuan $pertemuan, string $token): void
    {
        $tersimpan = Cache::get($this->kunciTantangan($mahasiswa, $pertemuan));

        if (! is_string($tersimpan) || ! hash_equals($tersimpan, hash('sha256', $token))) {
            throw ValidationException::withMessages([
                'presensi' => 'Tantangan presensi sudah digunakan atau kedaluwarsa. Muat ulang halaman.',
            ]);
        }
    }

    private function kunciTantangan(Mahasiswa $mahasiswa, Pertemuan $pertemuan): string
    {
        return "presensi:tantangan:{$mahasiswa->IDMAHASISWA}:{$pertemuan->IDPERTEMUAN}";
    }

    private function hitungJarakMeter(float $lintang1, float $bujur1, float $lintang2, float $bujur2): float
    {
        $selisihLintang = deg2rad($lintang2 - $lintang1);
        $selisihBujur = deg2rad($bujur2 - $bujur1);
        $a = sin($selisihLintang / 2) ** 2
            + cos(deg2rad($lintang1)) * cos(deg2rad($lintang2)) * sin($selisihBujur / 2) ** 2;
        $a = min(1, max(0, $a));

        return 6_371_000 * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    private function hitungSkorRisiko(float $jarak, float $radius, float $akurasi, float $skorWajah): int
    {
        $skorJarak = min(25, ($jarak / max(1, $radius)) * 25);
        $akurasiMaksimum = max(1, (float) config('presensi.akurasi_maksimum_meter'));
        $skorAkurasi = min(25, ($akurasi / $akurasiMaksimum) * 25);
        $skorWajahRisiko = (1 - $skorWajah) * 50;

        return (int) min(100, round($skorJarak + $skorAkurasi + $skorWajahRisiko));
    }

    private function tentukanStatus(float $jarak, float $radius, float $akurasi, float $skorWajah, int $skorRisiko): string
    {
        if ($jarak > $radius || $skorWajah < (float) config('presensi.ambang_wajah_tolak')) {
            return 'DITOLAK';
        }

        if (
            $akurasi > (float) config('presensi.akurasi_maksimum_meter')
            || $skorWajah < (float) config('presensi.ambang_wajah_hadir')
            || $skorRisiko >= (int) config('presensi.ambang_risiko_review')
        ) {
            return 'PERLUREVIEW';
        }

        return 'HADIR';
    }
}
