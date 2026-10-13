<?php

namespace Tests\Feature;

use App\Models\Mahasiswa;
use App\Models\Pengguna;
use App\Models\Pengumpulan;
use App\Models\Tugas;
use Database\Seeders\DataDemoSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

// ==========================================
// PEMBAHASAN / BAGIAN: Uji Tugas Dosen dan Isolasi Berkas
// ==========================================
class ModulDosenSesiEmpatTest extends TestCase
{
    use DatabaseTransactions;

    // ==========================================
    // PEMBAHASAN / BAGIAN: Tugas Dapat Dinilai dan Berkas Terbatas ke Pengampu
    // ==========================================
    public function test_dosen_dapat_membuat_tugas_mengunduh_dan_menilai_pengumpulan_miliknya(): void
    {
        Storage::fake('local');
        $this->seed(DataDemoSeeder::class);
        $penggunaDosen = Pengguna::query()->where('USERNAME', 'DOSEN002')->firstOrFail();
        $jadwal = $penggunaDosen->dosen->jadwal()->firstOrFail();

        $this->actingAs($penggunaDosen)
            ->withSession(['PERANAKTIF' => 'DOSEN'])
            ->get(route('dosen.rekap.indeks'))
            ->assertOk();

        $this->get(route('dosen.tugas.indeks'))->assertOk();

        $this->actingAs($penggunaDosen)
            ->withSession(['PERANAKTIF' => 'DOSEN'])
            ->post(route('dosen.tugas.simpan'), [
                'IDJADWAL' => $jadwal->IDJADWAL,
                'JUDULTUGAS' => 'Latihan Pengolahan Data',
                'DESKRIPSI' => 'Kerjakan secara mandiri.',
                'TANGGALTENGGAT' => now()->addDays(3)->format('Y-m-d H:i:s'),
                'MODEPENGERJAAN' => 'WAJIBKELAS',
            ])
            ->assertRedirect(route('dosen.tugas.indeks'));

        $tugas = Tugas::query()->where('JUDULTUGAS', 'Latihan Pengolahan Data')->firstOrFail();
        $mahasiswa = Mahasiswa::query()->firstOrFail();
        Storage::disk('local')->put('pengumpulan/latihan.pdf', 'isi berkas uji');
        $pengumpulan = Pengumpulan::query()->create([
            'IDTUGAS' => $tugas->IDTUGAS,
            'IDMAHASISWA' => $mahasiswa->IDMAHASISWA,
            'STATUSPENGUMPULAN' => 'SUDAH',
            'FILEKUMPUL' => 'pengumpulan/latihan.pdf',
            'WAKTUKUMPUL' => now(),
        ]);

        $this->get(route('dosen.tugas.unduh', $pengumpulan))
            ->assertOk()
            ->assertStreamedContent('isi berkas uji');

        $this->post(route('dosen.tugas.nilai', $pengumpulan), [
            'NILAI' => 88.5,
            'UMPANBALIK' => 'Analisis sudah baik.',
        ])->assertRedirect(route('dosen.tugas.indeks'));

        $pengumpulan->refresh();
        $this->assertSame('DINILAI', $pengumpulan->STATUSPENGUMPULAN);
        $this->assertSame(88.5, $pengumpulan->NILAI);
        $this->assertSame('Analisis sudah baik.', $pengumpulan->UMPANBALIK);

        $penggunaLain = Pengguna::query()->where('USERNAME', 'DOSEN003')->firstOrFail();
        $this->actingAs($penggunaLain)
            ->withSession(['PERANAKTIF' => 'DOSEN'])
            ->post(route('dosen.tugas.nilai', $pengumpulan), ['NILAI' => 90])
            ->assertForbidden();
    }
}
