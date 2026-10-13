<?php

namespace Tests\Feature;

use App\Models\Administrasi;
use App\Models\Pengguna;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

// ==========================================
// PEMBAHASAN / BAGIAN: Pengujian Form dan Riwayat Pengajuan Surat
// ==========================================
class PengajuanSuratTest extends TestCase
{
    use DatabaseTransactions;

    // ==========================================
    // PEMBAHASAN / BAGIAN: Surat Aktif dengan Lampiran dan Status Awal
    // ==========================================
    public function test_mahasiswa_dapat_mengajukan_surat_aktif_dan_melihat_riwayatnya(): void
    {
        Storage::fake('local');
        $pengguna = Pengguna::query()->where('USERNAME', '220101003')->firstOrFail();
        $this->actingAs($pengguna)->withSession(['PERANAKTIF' => 'MAHASISWA']);

        $this->get(route('mahasiswa.surat.indeks'))
            ->assertOk()
            ->assertSee('Riwayat Pengajuan Saya');

        $this->post(route('mahasiswa.surat.simpan'), [
            'JENISSURAT' => 'SURATAKTIF',
            'PERIHAL' => 'Surat keterangan mahasiswa aktif',
            'KEPERLUAN' => 'Pengajuan beasiswa',
            'FILELAMPIRAN' => UploadedFile::fake()->create('dokumen.pdf', 32, 'application/pdf'),
        ])->assertRedirect(route('mahasiswa.surat.indeks'));

        $surat = Administrasi::query()
            ->where('IDPEMOHON', $pengguna->IDPENGGUNA)
            ->where('PERIHAL', 'Surat keterangan mahasiswa aktif')
            ->firstOrFail();
        $this->assertSame('DIAJUKAN', $surat->STATUSADMINISTRASI);
        $this->assertSame([], $surat->DATAFORMULIR);
        Storage::disk('local')->assertExists($surat->FILELAMPIRAN);

        $this->get(route('mahasiswa.surat.indeks'))
            ->assertOk()
            ->assertSee('Surat keterangan mahasiswa aktif')
            ->assertSee('DIAJUKAN');
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Validasi Formulir Magang dan Pembatasan Kunci JSON
    // ==========================================
    public function test_pengajuan_magang_mewajibkan_data_dan_menolak_kunci_tambahan(): void
    {
        $pengguna = Pengguna::query()->where('USERNAME', '220101003')->firstOrFail();
        $this->actingAs($pengguna)->withSession(['PERANAKTIF' => 'MAHASISWA']);

        $this->postJson(route('mahasiswa.surat.simpan'), [
            'JENISSURAT' => 'MAGANG',
            'PERIHAL' => 'Surat pengantar magang',
            'KEPERLUAN' => 'Keperluan magang',
            'DATAFORMULIR' => [
                'NAMAPERUSAHAAN' => 'PT Contoh',
                'ALAMATPERUSAHAAN' => 'Jalan Kampus 1',
                'TANGGALMULAI' => '2026-07-01',
                'TANGGALSELESAI' => '2026-08-01',
                'KUNCITIDAKDIIZINKAN' => 'nilai',
            ],
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['DATAFORMULIR']);

        $this->assertDatabaseMissing('ADMINISTRASI', [
            'IDPEMOHON' => $pengguna->IDPENGGUNA,
            'PERIHAL' => 'Surat pengantar magang',
        ]);
    }
}
