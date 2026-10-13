<?php

namespace Tests\Feature;

use App\Models\Administrasi;
use App\Models\Pengguna;
use Database\Seeders\DataDemoSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LogicException;
use Tests\TestCase;

// ==========================================
// PEMBAHASAN / BAGIAN: Uji Tindakan Verifikasi BAAK
// ==========================================
class SuratSesiEmpatTest extends TestCase
{
    use DatabaseTransactions;

    // ==========================================
    // PEMBAHASAN / BAGIAN: Revisi Memerlukan Catatan dan Tidak Membekukan Isi
    // ==========================================
    public function test_baak_dapat_meminta_revisi_dengan_catatan(): void
    {
        $this->seed(DataDemoSeeder::class);
        $pemohon = Pengguna::query()->where('USERNAME', '220101001')->firstOrFail();
        $baak = Pengguna::query()->where('USERNAME', 'BAAK001')->firstOrFail();
        $surat = Administrasi::query()->create([
            'IDPEMOHON' => $pemohon->IDPENGGUNA,
            'JENISSURAT' => 'MAGANG',
            'PERIHAL' => 'Surat Pengantar Magang',
            'KEPERLUAN' => 'Pengajuan magang',
            'STATUSADMINISTRASI' => 'DIAJUKAN',
        ]);

        $this->actingAs($baak)
            ->withSession(['PERANAKTIF' => 'BAAK'])
            ->post(route('baak.surat.tinjau', $surat), [
                'KEPUTUSAN' => 'REVISI',
                'CATATAN' => 'Mohon lengkapi dokumen pendukung.',
            ])
            ->assertRedirect(route('baak.surat.indeks'));

        $surat->refresh();
        $this->assertSame('PERLUREVISI', $surat->STATUSADMINISTRASI);
        $this->assertSame('Mohon lengkapi dokumen pendukung.', $surat->CATATAN);
        $this->assertNull($surat->HASHISI);
        $this->assertDatabaseHas('RIWAYATTINDAKANSURAT', [
            'IDADMINISTRASI' => $surat->IDADMINISTRASI,
            'IDPELAKU' => $baak->IDPENGGUNA,
            'TINDAKAN' => 'BAAK_REVISI',
        ]);
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Mencegah Perubahan Isi Setelah Dibekukan
    // ==========================================
    public function test_isi_surat_yang_sudah_dibekukan_tidak_dapat_diubah(): void
    {
        $this->seed(DataDemoSeeder::class);
        $pemohon = Pengguna::query()->where('USERNAME', '220101001')->firstOrFail();
        $surat = Administrasi::query()->create([
            'IDPEMOHON' => $pemohon->IDPENGGUNA,
            'JENISSURAT' => 'SURATAKTIF',
            'PERIHAL' => 'Surat Aktif',
            'KEPERLUAN' => 'Beasiswa',
            'STATUSADMINISTRASI' => 'MENUNGGUPERSETUJUAN',
        ]);
        $isiKanonik = '{"PERIHAL":"Surat Aktif"}';
        $surat->forceFill([
            'NOMORSURAT' => 'SCT/2026/999999',
            'ISISURATKANONIK' => $isiKanonik,
            'HASHISI' => hash('sha256', $isiKanonik),
        ])->save();
        $surat->PERIHAL = 'Perubahan yang tidak diizinkan';

        $this->expectException(LogicException::class);
        $surat->save();
    }
}
