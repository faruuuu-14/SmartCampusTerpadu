<?php

namespace Tests\Feature;

use App\Models\Administrasi;
use App\Models\Pengguna;
use Database\Seeders\DataDemoSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

// ==========================================
// PEMBAHASAN / BAGIAN: Pengujian Verifikasi Publik dan Keamanan TTE
// ==========================================
class TandaTanganElektronikTest extends TestCase
{
    use DatabaseTransactions;

    // ==========================================
    // PEMBAHASAN / BAGIAN: Menyiapkan Data Surat Menunggu Persetujuan
    // ==========================================
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->seed(DataDemoSeeder::class);
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Memverifikasi TTE Asli Dua Penyetuju
    // ==========================================
    public function test_tte_asli_diverifikasi_valid_dengan_semua_penandatangan(): void
    {
        $surat = $this->terbitkanSurat();
        $persetujuan = $surat->persetujuanSurat()->orderBy('URUTAN')->firstOrFail();

        $this->get(route('tte.verifikasi', $persetujuan->TOKENVERIFIKASI))
            ->assertOk()
            ->assertSeeText('Tanda tangan Ed25519: VALID')
            ->assertSeeText('Sidik jari kunci: COCOK')
            ->assertSeeText('SHA-256 isi dan HASHISI pada muatan: COCOK')
            ->assertSeeText('Status akhir TTE: VALID')
            ->assertSeeText('Prof. Dr. Ir. Budi Santoso, M.T.')
            ->assertSeeText('Dr. Maya Prameswari, M.Si.');
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Mendeteksi Perubahan Isi Surat yang Dibekukan
    // ==========================================
    public function test_satu_karakter_isi_surat_diubah_menghasilkan_tidak_valid(): void
    {
        $surat = $this->terbitkanSurat();
        DB::table('ADMINISTRASI')->where('IDADMINISTRASI', $surat->IDADMINISTRASI)
            ->update(['ISISURATKANONIK' => $surat->ISISURATKANONIK.'x']);

        $this->get(route('tte.verifikasi', $surat->persetujuanSurat()->firstOrFail()->TOKENVERIFIKASI))
            ->assertOk()
            ->assertSeeText('TIDAK VALID')
            ->assertSeeText('Hash isi surat tidak cocok');
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Mendeteksi Perubahan Muatan yang Ditandatangani
    // ==========================================
    public function test_muatan_ttd_diubah_menghasilkan_tidak_valid(): void
    {
        $surat = $this->terbitkanSurat();
        $persetujuan = $surat->persetujuanSurat()->orderBy('URUTAN')->firstOrFail();
        $muatan = json_decode($persetujuan->MUATANTTD, true, 512, JSON_THROW_ON_ERROR);
        $muatan['HASHISI'] = str_repeat('0', 64);
        DB::table('PERSETUJUANSURAT')->where('IDPERSETUJUAN', $persetujuan->IDPERSETUJUAN)
            ->update(['MUATANTTD' => json_encode($muatan, JSON_THROW_ON_ERROR)]);

        $this->get(route('tte.verifikasi', $persetujuan->TOKENVERIFIKASI))
            ->assertOk()
            ->assertSeeText('TIDAK VALID')
            ->assertSeeText('Tanda tangan detached Ed25519 tidak valid');
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Mendeteksi Tanda Tangan Digital yang Diubah
    // ==========================================
    public function test_tanda_tangan_diubah_menghasilkan_tidak_valid(): void
    {
        $surat = $this->terbitkanSurat();
        $persetujuan = $surat->persetujuanSurat()->orderBy('URUTAN')->firstOrFail();
        DB::table('PERSETUJUANSURAT')->where('IDPERSETUJUAN', $persetujuan->IDPERSETUJUAN)
            ->update(['TANDATANGAN' => base64_encode(random_bytes(SODIUM_CRYPTO_SIGN_BYTES))]);

        $this->get(route('tte.verifikasi', $persetujuan->TOKENVERIFIKASI))
            ->assertOk()
            ->assertSeeText('TIDAK VALID')
            ->assertSeeText('Tanda tangan Ed25519: TIDAK VALID');
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Menampilkan Pesan untuk Token Tidak Terdaftar
    // ==========================================
    public function test_token_acak_ditolak_dengan_pesan_yang_jelas(): void
    {
        $this->get(route('tte.verifikasi', str()->uuid()))
            ->assertNotFound()
            ->assertSeeText('Token verifikasi tidak ditemukan');
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Membandingkan PDF Asli dan PDF yang Telah Diubah
    // ==========================================
    public function test_pdf_asli_sama_dan_pdf_yang_diubah_berbeda(): void
    {
        $surat = $this->terbitkanSurat();
        $persetujuan = $surat->persetujuanSurat()->firstOrFail();
        $pdfAsli = Storage::disk('local')->get($surat->FILEAKHIR);
        $url = route('tte.verifikasi.bandingkan', $persetujuan->TOKENVERIFIKASI);

        $this->post($url, ['BERKASPDF' => UploadedFile::fake()->createWithContent('surat.pdf', $pdfAsli)])
            ->assertRedirect(route('tte.verifikasi', $persetujuan->TOKENVERIFIKASI))
            ->assertSessionHas('perbandingan_pdf.hasil', 'SAMA');

        $this->post($url, ['BERKASPDF' => UploadedFile::fake()->createWithContent('surat-ubah.pdf', $pdfAsli.'ubah')])
            ->assertRedirect(route('tte.verifikasi', $persetujuan->TOKENVERIFIKASI))
            ->assertSessionHas('perbandingan_pdf.hasil', 'BERBEDA');
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Menolak Tanda Tangan Tanpa Autentikasi Ulang
    // ==========================================
    public function test_penyetuju_tanpa_autentikasi_ulang_gagal_menandatangani(): void
    {
        $surat = $this->suratMenungguPersetujuan();
        $penyetujuPertama = $surat->persetujuanSurat()->orderBy('URUTAN')->firstOrFail();
        $akun = Pengguna::query()->findOrFail($penyetujuPertama->IDPENYETUJU);

        $this->actingAs($akun)->withSession(['PERANAKTIF' => 'PIMPINAN'])
            ->post(route('pimpinan.surat.setujui', $penyetujuPertama), [])
            ->assertSessionHasErrors('PASSWORD');

        $this->assertNull($penyetujuPertama->fresh()->TANDATANGAN);
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Menolak Akun Tanpa Hak Pimpinan
    // ==========================================
    public function test_akun_tanpa_hak_pimpinan_ditolak(): void
    {
        $surat = $this->suratMenungguPersetujuan();
        $penyetuju = $surat->persetujuanSurat()->orderBy('URUTAN')->firstOrFail();
        $dosenBiasa = Pengguna::query()->where('USERNAME', 'DOSEN002')->firstOrFail();

        $this->actingAs($dosenBiasa)->withSession(['PERANAKTIF' => 'PIMPINAN'])
            ->post(route('pimpinan.surat.setujui', $penyetuju), ['PASSWORD' => 'SmartCampus2026!'])
            ->assertRedirect(route('login'));
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Membuat Surat yang Telah Lolos Pemeriksaan BAAK
    // ==========================================
    private function suratMenungguPersetujuan(): Administrasi
    {
        $mahasiswa = Pengguna::query()->where('USERNAME', '220101001')->firstOrFail();
        $baak = Pengguna::query()->where('USERNAME', 'BAAK001')->firstOrFail();
        $surat = Administrasi::query()->create([
            'IDPEMOHON' => $mahasiswa->IDPENGGUNA,
            'JENISSURAT' => 'SURATAKTIF',
            'PERIHAL' => 'Surat Aktif Uji',
            'KEPERLUAN' => 'Pengujian verifikasi publik',
            'DATAFORMULIR' => ['tujuan' => 'Pengujian'],
            'STATUSADMINISTRASI' => 'DIAJUKAN',
        ]);

        $this->actingAs($baak)->withSession(['PERANAKTIF' => 'BAAK'])
            ->post(route('baak.surat.tinjau', $surat), [
                'KEPUTUSAN' => 'LANJUT',
                'CEKLIST' => ['IDENTITAS', 'KEPERLUAN', 'LAMPIRAN'],
            ])
            ->assertRedirect(route('baak.surat.indeks'));

        return $surat->fresh();
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Menandatangani Surat oleh Kedua Pimpinan Berurutan
    // ==========================================
    private function terbitkanSurat(): Administrasi
    {
        $surat = $this->suratMenungguPersetujuan();
        $persetujuan = $surat->persetujuanSurat()->orderBy('URUTAN')->get();
        foreach ($persetujuan as $baris) {
            $akun = Pengguna::query()->findOrFail($baris->IDPENYETUJU);
            $this->actingAs($akun)->withSession(['PERANAKTIF' => 'PIMPINAN'])
                ->post(route('pimpinan.surat.setujui', $baris), ['PASSWORD' => 'SmartCampus2026!'])
                ->assertRedirect(route('pimpinan.surat.indeks'));
        }

        return $surat->fresh();
    }
}
