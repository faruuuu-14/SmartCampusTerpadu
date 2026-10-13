<?php

namespace Tests\Feature;

use App\Layanan\TandaTanganElektronik\PemverifikasiTte;
use App\Models\Administrasi;
use App\Models\Pengguna;
use Database\Seeders\DataDemoSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

// ==========================================
// PEMBAHASAN / BAGIAN: Uji Alur Lengkap TTE Dua Pimpinan
// ==========================================
class TteWorkflowTest extends TestCase
{
    use DatabaseTransactions;

    // ==========================================
    // PEMBAHASAN / BAGIAN: Menguji Alur BAAK, Dua TTE, PDF, dan QR
    // ==========================================
    public function test_surat_dibekukan_disetujui_berantai_dan_terbit_dengan_pdf_terverifikasi(): void
    {
        Storage::fake('local');
        $this->seed(DataDemoSeeder::class);
        $mahasiswa = Pengguna::query()->where('USERNAME', '220101001')->firstOrFail();
        $baak = Pengguna::query()->where('USERNAME', 'BAAK001')->firstOrFail();
        $pimpinanLevelDua = Pengguna::query()->where('USERNAME', 'DOSEN004')->firstOrFail();
        $pimpinanLevelTiga = Pengguna::query()->where('USERNAME', 'DOSEN001')->firstOrFail();
        $surat = Administrasi::query()->create([
            'IDPEMOHON' => $mahasiswa->IDPENGGUNA,
            'JENISSURAT' => 'SURATAKTIF',
            'PERIHAL' => 'Surat Keterangan Mahasiswa Aktif',
            'KEPERLUAN' => 'Persyaratan beasiswa',
            'DATAFORMULIR' => ['tujuan' => 'Program Beasiswa', 'nomor' => 'A-001'],
            'STATUSADMINISTRASI' => 'DIAJUKAN',
        ]);

        $this->actingAs($baak)
            ->withSession(['PERANAKTIF' => 'BAAK'])
            ->get(route('baak.surat.indeks'))
            ->assertOk()
            ->assertSee('Surat Keterangan Mahasiswa Aktif');

        $this->actingAs($baak)
            ->withSession(['PERANAKTIF' => 'BAAK'])
            ->post(route('baak.surat.tinjau', $surat), [
                'KEPUTUSAN' => 'LANJUT',
                'CEKLIST' => ['IDENTITAS', 'KEPERLUAN', 'LAMPIRAN'],
            ])
            ->assertRedirect(route('baak.surat.indeks'));

        $surat->refresh();
        $this->assertSame('MENUNGGUPERSETUJUAN', $surat->STATUSADMINISTRASI);
        $this->assertSame(hash('sha256', $surat->ISISURATKANONIK), $surat->HASHISI);
        $this->assertStringContainsString('SCT/', $surat->NOMORSURAT);
        $persetujuan = $surat->persetujuanSurat()->orderBy('URUTAN')->get();
        $this->assertCount(2, $persetujuan);
        $this->assertSame($pimpinanLevelDua->IDPENGGUNA, $persetujuan[0]->IDPENYETUJU);
        $this->assertSame($pimpinanLevelTiga->IDPENGGUNA, $persetujuan[1]->IDPENYETUJU);

        $this->actingAs($pimpinanLevelDua)
            ->withSession(['PERANAKTIF' => 'PIMPINAN'])
            ->get(route('pimpinan.surat.indeks'))
            ->assertOk()
            ->assertSee($surat->NOMORSURAT);

        $this->actingAs($pimpinanLevelTiga)
            ->withSession(['PERANAKTIF' => 'PIMPINAN'])
            ->post(route('pimpinan.surat.setujui', $persetujuan[1]), ['PASSWORD' => 'SmartCampus2026!'])
            ->assertForbidden();

        $this->actingAs($pimpinanLevelDua)
            ->withSession(['PERANAKTIF' => 'PIMPINAN'])
            ->post(route('pimpinan.surat.setujui', $persetujuan[0]), ['PASSWORD' => 'salah'])
            ->assertSessionHasErrors('PASSWORD');

        $this->post(route('pimpinan.surat.setujui', $persetujuan[0]), ['PASSWORD' => 'SmartCampus2026!'])
            ->assertRedirect(route('pimpinan.surat.indeks'));
        $persetujuan[0]->refresh();
        $surat->refresh();
        $this->assertSame('DISETUJUI', $persetujuan[0]->STATUSPERSETUJUAN);
        $this->assertSame('MENUNGGUPERSETUJUAN', $surat->STATUSADMINISTRASI);
        $this->assertNull($surat->FILEAKHIR);

        $this->actingAs($pimpinanLevelTiga)
            ->withSession(['PERANAKTIF' => 'PIMPINAN'])
            ->post(route('pimpinan.surat.setujui', $persetujuan[1]), ['PASSWORD' => 'SmartCampus2026!'])
            ->assertRedirect(route('pimpinan.surat.indeks'));

        $surat->refresh();
        $persetujuan = $surat->persetujuanSurat()->with('penyetuju')->orderBy('URUTAN')->get();
        $this->assertSame('TERBIT', $surat->STATUSADMINISTRASI);
        $this->assertNotNull($surat->TANGGALTERBIT);
        $this->assertCount(2, $persetujuan->where('STATUSPERSETUJUAN', 'DISETUJUI'));
        $this->assertTrue(Storage::disk('local')->exists($surat->FILEAKHIR));
        $this->assertSame($surat->HASHBERKAS, hash('sha256', Storage::disk('local')->get($surat->FILEAKHIR)));
        $this->assertStringStartsWith('%PDF', Storage::disk('local')->get($surat->FILEAKHIR));

        $verifikasi = app(PemverifikasiTte::class);
        $this->assertTrue($verifikasi->verifikasi($persetujuan[0]));
        $urutanTersimpan = $persetujuan[0]->URUTAN;
        $persetujuan[0]->URUTAN = 99;
        $this->assertFalse($verifikasi->verifikasi($persetujuan[0]));
        $persetujuan[0]->URUTAN = $urutanTersimpan;
        $this->get(route('tte.verifikasi', $persetujuan[0]->TOKENVERIFIKASI))
            ->assertOk()
            ->assertSee('VALID');

        $isiAsli = $persetujuan[0]->administrasi->ISISURATKANONIK;
        $persetujuan[0]->administrasi->ISISURATKANONIK = $isiAsli.' ';
        $this->assertFalse($verifikasi->verifikasi($persetujuan[0]));

        $this->assertSame(2, $surat->riwayatTindakan()->where('TINDAKAN', 'TTE_SETUJU')->count());
    }
}
