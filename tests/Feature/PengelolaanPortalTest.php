<?php

namespace Tests\Feature;

use App\Models\Jadwal;
use App\Models\Krs;
use App\Models\Pengguna;
use App\Models\Tugas;
use Database\Seeders\DataDemoSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

// ==========================================
// PEMBAHASAN / BAGIAN: Pengujian CRUD Portal Pengguna dan Tugas
// ==========================================
class PengelolaanPortalTest extends TestCase
{
    use DatabaseTransactions;

    // ==========================================
    // PEMBAHASAN / BAGIAN: Menyiapkan Akun Demo untuk Tes Portal
    // ==========================================
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DataDemoSeeder::class);
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Menguji CRUD Akun Mahasiswa melalui Portal BAAK
    // ==========================================
    public function test_baak_dapat_membuat_memperbarui_dan_menonaktifkan_akun_mahasiswa(): void
    {
        $baak = Pengguna::query()->where('USERNAME', 'BAAK001')->firstOrFail();
        $this->actingAs($baak)->withSession(['PERANAKTIF' => 'BAAK']);
        $this->get(route('baak.pengguna.index'))->assertOk()->assertSee('Tambah akun');

        $this->post(route('baak.pengguna.store'), [
            'JENISPERAN' => 'MAHASISWA',
            'USERNAME' => '240101099',
            'PASSWORD' => 'KataSandiAkunBaru2026!',
            'PASSWORD_confirmation' => 'KataSandiAkunBaru2026!',
            'NAMALENGKAP' => 'Mahasiswa Uji Portal',
            'EMAIL' => 'mahasiswa.uji@smartcampus.ac.id',
            'NOMORTELEPON' => '6289988776655',
            'STATUSAKUN' => 'AKTIF',
            'NIM' => '240101099',
            'PROGRAMSTUDI' => 'Teknik Informatika',
            'ANGKATAN' => '2024',
        ])->assertRedirect(route('baak.pengguna.index'));

        $akun = Pengguna::query()->where('USERNAME', '240101099')->firstOrFail();
        $this->assertDatabaseHas('MAHASISWA', [
            'IDPENGGUNA' => $akun->IDPENGGUNA,
            'NIM' => '240101099',
            'NAMAMAHASISWA' => 'Mahasiswa Uji Portal',
        ]);

        $this->put(route('baak.pengguna.update', $akun), [
            'USERNAME' => '240101099',
            'NAMALENGKAP' => 'Mahasiswa Portal Diperbarui',
            'EMAIL' => 'mahasiswa.uji@smartcampus.ac.id',
            'NOMORTELEPON' => '6289988776655',
            'STATUSAKUN' => 'AKTIF',
            'PASSWORD' => '',
            'PASSWORD_confirmation' => '',
            'NIM' => '240101099',
            'PROGRAMSTUDI' => 'Sistem Informasi',
            'ANGKATAN' => '2024',
        ])->assertRedirect(route('baak.pengguna.index'));

        $this->assertDatabaseHas('MAHASISWA', [
            'IDPENGGUNA' => $akun->IDPENGGUNA,
            'PROGRAMSTUDI' => 'Sistem Informasi',
        ]);

        $this->delete(route('baak.pengguna.destroy', $akun))
            ->assertRedirect(route('baak.pengguna.index'));

        $this->assertDatabaseHas('PENGGUNA', [
            'IDPENGGUNA' => $akun->IDPENGGUNA,
            'STATUSAKUN' => 'NONAKTIF',
        ]);
        $this->assertDatabaseHas('MAHASISWA', ['IDPENGGUNA' => $akun->IDPENGGUNA]);
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Menolak CRUD BAAK dari Peran Mahasiswa
    // ==========================================
    public function test_mahasiswa_tidak_dapat_membuka_pengelolaan_pengguna_baak(): void
    {
        $mahasiswa = Pengguna::query()->where('USERNAME', '220101002')->firstOrFail();

        $this->actingAs($mahasiswa)
            ->withSession(['PERANAKTIF' => 'MAHASISWA'])
            ->get(route('baak.pengguna.index'))
            ->assertRedirect(route('login'));
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Menolak Pembuatan Dosen dengan Hak Pimpinan Tersembunyi
    // ==========================================
    public function test_baak_tidak_dapat_menyisipkan_jabatan_pimpinan_ke_akun_dosen_biasa(): void
    {
        $baak = Pengguna::query()->where('USERNAME', 'BAAK001')->firstOrFail();

        $this->actingAs($baak)->withSession(['PERANAKTIF' => 'BAAK'])
            ->post(route('baak.pengguna.store'), [
                'JENISPERAN' => 'DOSEN',
                'USERNAME' => 'DOSENBARU',
                'PASSWORD' => 'KataSandiAkunBaru2026!',
                'PASSWORD_confirmation' => 'KataSandiAkunBaru2026!',
                'NAMALENGKAP' => 'Dosen Baru',
                'STATUSAKUN' => 'AKTIF',
                'NIDN' => '0099887766',
                'PROGRAMSTUDI' => 'Teknik Informatika',
                'JABATAN' => 'Dekan',
                'LEVELPERSETUJUAN' => 1,
            ])
            ->assertSessionHasErrors(['JABATAN', 'LEVELPERSETUJUAN']);

        $this->assertDatabaseMissing('PENGGUNA', ['USERNAME' => 'DOSENBARU']);
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Menguji Lihat dan CRUD Pengumpulan oleh Mahasiswa
    // ==========================================
    public function test_mahasiswa_dapat_melihat_mengumpulkan_dan_menghapus_tugas_kelasnya(): void
    {
        Storage::fake('local');
        $mahasiswa = Pengguna::query()->where('USERNAME', '220101001')->firstOrFail();
        $jadwal = $mahasiswa->mahasiswa->krs()->firstOrFail()->jadwal;
        $dosen = $jadwal->dosen;
        $tugas = $dosen->tugas()->create([
            'IDJADWAL' => $jadwal->IDJADWAL,
            'JUDULTUGAS' => 'Tugas Uji Portal Mahasiswa',
            'DESKRIPSI' => 'Kumpulkan dokumen.',
            'TANGGALTENGGAT' => now()->addDays(3),
            'MODEPENGERJAAN' => 'WAJIBKELAS',
            'DIBUATVIA' => 'WEB',
        ]);

        $this->actingAs($mahasiswa)->withSession(['PERANAKTIF' => 'MAHASISWA'])
            ->get(route('mahasiswa.tugas.indeks'))
            ->assertOk()
            ->assertSee('Tugas Uji Portal Mahasiswa');

        $this->post(route('mahasiswa.tugas.simpan', $tugas), [
            'BERKAS' => UploadedFile::fake()->create('hasil.pdf', 64, 'application/pdf'),
        ])->assertRedirect(route('mahasiswa.tugas.indeks'));

        $this->assertDatabaseHas('PENGUMPULAN', [
            'IDTUGAS' => $tugas->IDTUGAS,
            'IDMAHASISWA' => $mahasiswa->mahasiswa->IDMAHASISWA,
            'STATUSPENGUMPULAN' => 'SUDAH',
        ]);
        $pengumpulan = $tugas->pengumpulan()->firstOrFail();
        Storage::disk('local')->assertExists($pengumpulan->FILEKUMPUL);

        $this->delete(route('mahasiswa.tugas.hapus', $tugas))
            ->assertRedirect(route('mahasiswa.tugas.indeks'));
        $this->assertDatabaseMissing('PENGUMPULAN', ['IDPENGUMPULAN' => $pengumpulan->IDPENGUMPULAN]);
        Storage::disk('local')->assertMissing($pengumpulan->FILEKUMPUL);
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Menguji CRUD Jadwal Kuliah oleh BAAK
    // ==========================================
    public function test_baak_dapat_membuat_memperbarui_dan_menghapus_jadwal_kosong(): void
    {
        $this->seed(DataDemoSeeder::class);
        $baak = Pengguna::query()->where('USERNAME', 'BAAK001')->firstOrFail();
        $dosen = Pengguna::query()->where('USERNAME', 'DOSEN002')->firstOrFail()->dosen;
        $this->actingAs($baak)->withSession(['PERANAKTIF' => 'BAAK']);

        $dataJadwal = [
            'IDDOSEN' => $dosen->IDDOSEN,
            'KODEMATAKULIAH' => 'UTL901',
            'NAMAMATAKULIAH' => 'Uji CRUD Jadwal',
            'SKS' => 2,
            'KELAS' => 'UTL-A',
            'HARI' => 'KAMIS',
            'JAMMULAI' => '08:00',
            'JAMSELESAI' => '09:30',
            'RUANGAN' => 'Ruang Uji',
            'LINTANG' => -6.2,
            'BUJUR' => 106.8,
            'RADIUSMETER' => 100,
            'MODEKEHADIRAN' => 'WAJIBKELAS',
            'MENITPENGINGAT' => 15,
        ];

        $this->post(route('baak.jadwal.store'), $dataJadwal)
            ->assertRedirect(route('baak.jadwal.index'));
        $jadwal = Jadwal::query()->where('KODEMATAKULIAH', 'UTL901')->firstOrFail();

        $this->put(route('baak.jadwal.update', $jadwal), array_merge($dataJadwal, [
            'NAMAMATAKULIAH' => 'Uji CRUD Jadwal Diperbarui',
        ]))->assertRedirect(route('baak.jadwal.index'));
        $this->assertSame('Uji CRUD Jadwal Diperbarui', $jadwal->fresh()->NAMAMATAKULIAH);

        $this->delete(route('baak.jadwal.destroy', $jadwal))
            ->assertRedirect(route('baak.jadwal.index'));
        $this->assertDatabaseMissing('JADWAL', ['IDJADWAL' => $jadwal->IDJADWAL]);
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Menguji CRUD KRS dan Pencegahan Duplikasi
    // ==========================================
    public function test_baak_dapat_mengelola_krs_dan_mencegah_enrolmen_ganda(): void
    {
        $this->seed(DataDemoSeeder::class);
        $baak = Pengguna::query()->where('USERNAME', 'BAAK001')->firstOrFail();
        $mahasiswa = Pengguna::query()->where('USERNAME', '230201004')->firstOrFail()->mahasiswa;
        $jadwalPertama = Jadwal::query()->where('KODEMATAKULIAH', 'IF301')->firstOrFail();
        $jadwalKedua = Jadwal::query()->where('KODEMATAKULIAH', 'IF302')->firstOrFail();
        $jadwalDuplikat = Jadwal::query()->where('KODEMATAKULIAH', 'SI201')->firstOrFail();
        $this->actingAs($baak)->withSession(['PERANAKTIF' => 'BAAK']);

        $this->post(route('baak.krs.store'), [
            'IDMAHASISWA' => $mahasiswa->IDMAHASISWA,
            'IDJADWAL' => $jadwalPertama->IDJADWAL,
        ])->assertRedirect(route('baak.krs.index'));
        $krs = Krs::query()
            ->where('IDMAHASISWA', $mahasiswa->IDMAHASISWA)
            ->where('IDJADWAL', $jadwalPertama->IDJADWAL)
            ->firstOrFail();

        $this->post(route('baak.krs.store'), [
            'IDMAHASISWA' => $mahasiswa->IDMAHASISWA,
            'IDJADWAL' => $jadwalDuplikat->IDJADWAL,
        ])->assertSessionHasErrors('IDJADWAL');

        $this->put(route('baak.krs.update', $krs), [
            'IDMAHASISWA' => $mahasiswa->IDMAHASISWA,
            'IDJADWAL' => $jadwalKedua->IDJADWAL,
        ])->assertRedirect(route('baak.krs.index'));
        $this->assertSame($jadwalKedua->IDJADWAL, $krs->fresh()->IDJADWAL);

        $this->delete(route('baak.krs.destroy', $krs))
            ->assertRedirect(route('baak.krs.index'));
        $this->assertDatabaseMissing('KRS', ['IDKRS' => $krs->IDKRS]);
    }
}
