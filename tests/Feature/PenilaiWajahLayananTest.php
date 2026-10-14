<?php

// ==========================================
// PEMBAHASAN / BAGIAN: Pengujian Penilai Wajah dan Jalur Keputusan Presensi
// ==========================================

namespace Tests\Feature;

use App\Layanan\Presensi\HasilPenilaianWajah;
use App\Layanan\Presensi\KeputusanWajah;
use App\Layanan\Presensi\PenilaiWajahLayanan;
use App\Models\Mahasiswa;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PenilaiWajahLayananTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config([
            'wajah.url' => 'http://127.0.0.1:8765',
            'wajah.kunci' => 'kunci-pengujian',
            'wajah.ambang' => 0.7,
            'wajah.skor_di_bawah_ambang' => 'review',
        ]);
        Http::preventStrayRequests();
    }

    public function test_skor_cosine_valid_dikirim_dengan_kunci_dan_embedding_terenkripsi(): void
    {
        Http::fake([
            '127.0.0.1:8765/v1/verifikasi' => Http::response([
                'model' => 'FaceNet',
                'face_found' => true,
                'face_count' => 1,
                'score' => 0.91,
                'processing_ms' => 12.5,
            ]),
        ]);

        $hasil = app(PenilaiWajahLayanan::class)->nilaiTerperinci(
            UploadedFile::fake()->create('foto.jpg', 10, 'image/jpeg'),
            $this->mahasiswaAktif(),
        );

        $this->assertSame(0.91, $hasil->skor);
        $this->assertFalse($hasil->perluDitinjau());
        Http::assertSent(function ($permintaan): bool {
            $muatan = $permintaan->body();

            return $permintaan->hasHeader('X-KUNCI-LAYANAN', 'kunci-pengujian')
                && str_contains($muatan, 'name="model"')
                && str_contains($muatan, 'FaceNet')
                && str_contains($muatan, 'name="referensi_base64"')
                && str_contains($muatan, 'embedding-terdekripsi');
        });
    }

    public function test_tanpa_wajah_menghasilkan_jalur_review(): void
    {
        Http::fake([
            '*' => Http::response([
                'model' => 'FaceNet',
                'face_found' => false,
                'face_count' => 0,
                'score' => null,
                'processing_ms' => 4.0,
            ]),
        ]);
        $hasil = app(PenilaiWajahLayanan::class)->nilaiTerperinci(
            UploadedFile::fake()->create('foto.jpg', 10, 'image/jpeg'),
            $this->mahasiswaAktif(),
        );

        $this->assertNull($hasil->skor);
        $this->assertSame('wajah_tidak_ditemukan', $hasil->alasan);
        $this->assertSame('PERLUREVIEW', (new KeputusanWajah)->statusAwal($hasil, 0.7, 'tolak'));
    }

    public function test_banyak_wajah_menghasilkan_jalur_review(): void
    {
        Http::fake([
            '*' => Http::response([
                'model' => 'FaceNet',
                'face_found' => false,
                'face_count' => 2,
                'score' => null,
                'processing_ms' => 7.0,
            ]),
        ]);
        $hasil = app(PenilaiWajahLayanan::class)->nilaiTerperinci(
            UploadedFile::fake()->create('foto.jpg', 10, 'image/jpeg'),
            $this->mahasiswaAktif(),
        );

        $this->assertSame('lebih_dari_satu_wajah', $hasil->alasan);
        $this->assertSame('PERLUREVIEW', (new KeputusanWajah)->statusAwal($hasil, 0.7, 'tolak'));
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Respons Layanan Wajah yang Tidak Konsisten
    // ==========================================
    public function test_respons_wajah_kontradiktori_diarahkan_ke_review(): void
    {
        Http::fake([
            '*' => Http::response([
                'model' => 'FaceNet',
                'face_found' => false,
                'face_count' => 1,
                'score' => 0.99,
                'processing_ms' => 7.0,
            ]),
        ]);

        $hasil = app(PenilaiWajahLayanan::class)->nilaiTerperinci(
            UploadedFile::fake()->create('foto.jpg', 10, 'image/jpeg'),
            $this->mahasiswaAktif(),
        );

        $this->assertSame('respons_layanan_tidak_valid', $hasil->alasan);
        $this->assertSame('PERLUREVIEW', (new KeputusanWajah)->statusAwal($hasil, 0.7, 'tolak'));
    }

    public function test_skor_di_bawah_ambang_mengikuti_konfigurasi_review_atau_tolak(): void
    {
        $hasil = new HasilPenilaianWajah(0.4, 1, 'FaceNet', 10.0);
        $keputusan = new KeputusanWajah;

        $this->assertSame('PERLUREVIEW', $keputusan->statusAwal($hasil, 0.7, 'review'));
        $this->assertSame('DITOLAK', $keputusan->statusAwal($hasil, 0.7, 'tolak'));
    }

    public function test_skor_di_ambang_lanjut_ke_penilaian_risiko_dan_tanpa_ambang_review(): void
    {
        $hasil = new HasilPenilaianWajah(0.7, 1, 'FaceNet', 10.0);
        $keputusan = new KeputusanWajah;

        $this->assertNull($keputusan->statusAwal($hasil, 0.7, 'review'));
        $this->assertSame('PERLUREVIEW', $keputusan->statusAwal($hasil, null, 'review'));
    }

    public function test_gangguan_layanan_dialihkan_ke_review_dan_mencoba_satu_kali_ulang(): void
    {
        Http::fake(['*' => Http::failedConnection()]);

        $hasil = app(PenilaiWajahLayanan::class)->nilaiTerperinci(
            UploadedFile::fake()->create('foto.jpg', 10, 'image/jpeg'),
            $this->mahasiswaAktif(),
        );

        $this->assertSame('layanan_tidak_tersedia', $hasil->alasan);
        $this->assertSame('PERLUREVIEW', (new KeputusanWajah)->statusAwal($hasil, 0.7, 'tolak'));
        Http::assertSentCount(2);
    }

    public function test_embedding_mahasiswa_yang_belum_aktif_tidak_dikirim_ke_layanan(): void
    {
        $mahasiswa = $this->mahasiswaAktif();
        $mahasiswa->STATUSWAJAH = 'MENUNGGU_VERIFIKASI';

        $hasil = app(PenilaiWajahLayanan::class)->nilaiTerperinci(
            UploadedFile::fake()->create('foto.jpg', 10, 'image/jpeg'),
            $mahasiswa,
        );

        $this->assertSame('pendaftaran_belum_aktif', $hasil->alasan);
        $this->assertSame('PERLUREVIEW', (new KeputusanWajah)->statusAwal($hasil, 0.7, 'tolak'));
        Http::assertNothingSent();
    }

    private function mahasiswaAktif(): Mahasiswa
    {
        return new Mahasiswa([
            'IDMAHASISWA' => 1,
            'EMBEDDINGWAJAH' => Crypt::encryptString('embedding-terdekripsi'),
            'MODELWAJAH' => 'FaceNet',
            'STATUSWAJAH' => 'AKTIF',
            'TANGGALKONSENBIOMETRIK' => now(),
        ]);
    }
}
