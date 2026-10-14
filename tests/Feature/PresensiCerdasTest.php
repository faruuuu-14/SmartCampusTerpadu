<?php

namespace Tests\Feature;

use App\Models\Dosen;
use App\Models\Jadwal;
use App\Models\Krs;
use App\Models\Mahasiswa;
use App\Models\Pengguna;
use App\Models\Pertemuan;
use App\Models\Presensi;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Tests\TestCase;

// ==========================================
// PEMBAHASAN / BAGIAN: Pengujian Alur Presensi Cerdas
// ==========================================
class PresensiCerdasTest extends TestCase
{
    use DatabaseTransactions;

    // ==========================================
    // PEMBAHASAN / BAGIAN: Mengaktifkan Penilai Tiruan Khusus Pengujian Presensi
    // ==========================================
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'wajah.mode' => 'uji',
            'wajah.ambang' => 0.85,
            'presensi.ambang_wajah_hadir' => 0.85,
            'presensi.ambang_wajah_tolak' => 0.85,
            'presensi.penilai_wajah.skor_tiruan' => 0.93,
        ]);
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Pengelolaan Sesi Dosen dan Pembuatan Tautan
    // ==========================================
    public function test_dosen_dapat_membuka_melihat_tautan_dan_menutup_sesi(): void
    {
        $dosen = Dosen::query()->whereHas('pengguna', fn ($query) => $query->where('USERNAME', 'DOSEN002'))->firstOrFail();
        $pengguna = Pengguna::query()->findOrFail($dosen->IDPENGGUNA);
        $mahasiswa = Mahasiswa::query()->where('NIM', '220101003')->firstOrFail();
        $jadwal = $this->buatJadwal($dosen);
        Krs::query()->create([
            'IDJADWAL' => $jadwal->IDJADWAL,
            'IDMAHASISWA' => $mahasiswa->IDMAHASISWA,
        ]);

        $this->actingAs($pengguna)->withSession(['PERANAKTIF' => 'DOSEN']);
        $this->post(route('dosen.pertemuan.buka', $jadwal))
            ->assertRedirect();

        $pertemuan = Pertemuan::query()->where('IDJADWAL', $jadwal->IDJADWAL)->firstOrFail();
        $this->assertSame('BERLANGSUNG', $pertemuan->STATUSPERTEMUAN);
        $this->assertNotNull($pertemuan->BUKAPRESENSI);
        $this->assertNotNull($pertemuan->TUTUPPRESENSI);

        $this->get(route('dosen.pertemuan.indeks'))
            ->assertOk()
            ->assertSee('Buka tautan')
            ->assertSee('220101003');

        $this->post(route('dosen.pertemuan.tutup', $pertemuan))
            ->assertRedirect();
        $this->assertSame('SELESAI', $pertemuan->fresh()->STATUSPERTEMUAN);
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Kamera Bertanda Tangan, GPS, Foto Privat, dan Anti-Duplikasi
    // ==========================================
    public function test_tautan_signed_menyimpan_presensi_dan_mencegah_kiriman_ganda(): void
    {
        Storage::fake('local');
        $mahasiswa = Mahasiswa::query()->where('NIM', '220101003')->firstOrFail();
        $jadwal = $this->buatJadwal();
        Krs::query()->create([
            'IDJADWAL' => $jadwal->IDJADWAL,
            'IDMAHASISWA' => $mahasiswa->IDMAHASISWA,
        ]);
        $pertemuan = Pertemuan::query()->create([
            'IDJADWAL' => $jadwal->IDJADWAL,
            'PERTEMUANKE' => 1,
            'TANGGALPERTEMUAN' => today(),
            'STATUSPERTEMUAN' => 'BERLANGSUNG',
            'MODEKEHADIRAN' => 'WAJIBKELAS',
            'BUKAPRESENSI' => now()->subMinute(),
            'TUTUPPRESENSI' => now()->addHour(),
        ]);
        $url = URL::temporarySignedRoute(
            'mahasiswa.presensi.tampilkan',
            now()->addMinutes(5),
            ['mahasiswa' => $mahasiswa, 'pertemuan' => $pertemuan],
        );

        $responsTampil = $this->get($url);
        $responsTampil->assertOk()->assertSee('Tantangan liveness');
        $token = $responsTampil->original->getData()['tantangan']['token'];

        $payload = [
            'FOTOPRESENSI' => UploadedFile::fake()->create('kamera.jpg', 120, 'image/jpeg'),
            'LINTANG' => $jadwal->LINTANG,
            'BUJUR' => $jadwal->BUJUR,
            'AKURASIGPS' => 8,
            'TANTANGAN' => $token,
            'LIVENESSLOLOS' => '1',
        ];

        $kirimanUlang = $payload;
        $kirimanUlang['FOTOPRESENSI'] = UploadedFile::fake()->create('kamera-ulang.jpg', 120, 'image/jpeg');
        $this->post($url, $kirimanUlang, ['Accept' => 'application/json'])
            ->assertCreated()
            ->assertJsonPath('status', 'HADIR');

        $presensi = Presensi::query()
            ->where('IDPERTEMUAN', $pertemuan->IDPERTEMUAN)
            ->where('IDMAHASISWA', $mahasiswa->IDMAHASISWA)
            ->firstOrFail();
        $this->assertSame('HADIR', $presensi->STATUSPRESENSI);
        $this->assertTrue($presensi->LIVENESSLOLOS);
        $this->assertMatchesRegularExpression('/^[0-9a-f-]{36}\.jpg$/', basename($presensi->FILEFOTO));
        Storage::disk('local')->assertExists($presensi->FILEFOTO);

        $this->post($url, $payload, ['Accept' => 'application/json'])
            ->assertUnprocessable();
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Mode Kehadiran BEBAS dari Jadwal dan Radius Longgar
    // ==========================================
    public function test_mode_bebas_diambil_dari_jadwal_dan_memperluas_geofence(): void
    {
        Storage::fake('local');
        $mahasiswa = Mahasiswa::query()->where('NIM', '220101003')->firstOrFail();
        $jadwal = $this->buatJadwal(mode: 'BEBAS');
        Krs::query()->create([
            'IDJADWAL' => $jadwal->IDJADWAL,
            'IDMAHASISWA' => $mahasiswa->IDMAHASISWA,
        ]);
        $pertemuan = Pertemuan::query()->create([
            'IDJADWAL' => $jadwal->IDJADWAL,
            'PERTEMUANKE' => 1,
            'TANGGALPERTEMUAN' => today(),
            'STATUSPERTEMUAN' => 'BERLANGSUNG',
            'MODEKEHADIRAN' => null,
            'BUKAPRESENSI' => now()->subMinute(),
            'TUTUPPRESENSI' => now()->addHour(),
        ]);
        $url = URL::temporarySignedRoute(
            'mahasiswa.presensi.tampilkan',
            now()->addMinutes(5),
            ['mahasiswa' => $mahasiswa, 'pertemuan' => $pertemuan],
        );
        $tampilan = $this->get($url);
        $tampilan->assertOk();
        $token = $tampilan->original->getData()['tantangan']['token'];

        $this->post($url, [
            'FOTOPRESENSI' => UploadedFile::fake()->create('kamera.jpg', 120, 'image/jpeg'),
            'LINTANG' => (float) $jadwal->LINTANG + 0.002,
            'BUJUR' => $jadwal->BUJUR,
            'AKURASIGPS' => 8,
            'TANTANGAN' => $token,
            'LIVENESSLOLOS' => '1',
        ], ['Accept' => 'application/json'])
            ->assertCreated()
            ->assertJsonPath('status', 'HADIR');
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Tautan Tidak Dapat Dipakai Setelah Kedaluwarsa
    // ==========================================
    public function test_tautan_presensi_kedaluwarsa_setelah_lima_menit(): void
    {
        $mahasiswa = Mahasiswa::query()->where('NIM', '220101003')->firstOrFail();
        $jadwal = $this->buatJadwal();
        $pertemuan = Pertemuan::query()->create([
            'IDJADWAL' => $jadwal->IDJADWAL,
            'PERTEMUANKE' => 1,
            'TANGGALPERTEMUAN' => today(),
            'STATUSPERTEMUAN' => 'BERLANGSUNG',
            'MODEKEHADIRAN' => 'WAJIBKELAS',
            'BUKAPRESENSI' => now()->subMinute(),
            'TUTUPPRESENSI' => now()->addHour(),
        ]);
        $url = URL::temporarySignedRoute(
            'mahasiswa.presensi.tampilkan',
            now()->addMinutes(5),
            ['mahasiswa' => $mahasiswa, 'pertemuan' => $pertemuan],
        );

        $this->travel(6)->minutes();
        $this->get($url)->assertForbidden();
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Keputusan Dosen atas Presensi yang Perlu Ditinjau
    // ==========================================
    public function test_dosen_dapat_menyetujui_presensi_yang_menunggu_peninjauan(): void
    {
        $dosen = Dosen::query()->whereHas('pengguna', fn ($query) => $query->where('USERNAME', 'DOSEN002'))->firstOrFail();
        $pengguna = Pengguna::query()->findOrFail($dosen->IDPENGGUNA);
        $mahasiswa = Mahasiswa::query()->where('NIM', '220101003')->firstOrFail();
        $jadwal = $this->buatJadwal($dosen);
        $pertemuan = Pertemuan::query()->create([
            'IDJADWAL' => $jadwal->IDJADWAL,
            'PERTEMUANKE' => 1,
            'TANGGALPERTEMUAN' => today(),
            'STATUSPERTEMUAN' => 'BERLANGSUNG',
            'BUKAPRESENSI' => now()->subMinute(),
            'TUTUPPRESENSI' => now()->addHour(),
        ]);
        $presensi = Presensi::query()->create([
            'IDPERTEMUAN' => $pertemuan->IDPERTEMUAN,
            'IDMAHASISWA' => $mahasiswa->IDMAHASISWA,
            'WAKTUKIRIM' => now(),
            'SKORRISIKO' => 60,
            'STATUSPRESENSI' => 'PERLUREVIEW',
        ]);

        $this->actingAs($pengguna)->withSession(['PERANAKTIF' => 'DOSEN'])
            ->post(route('dosen.presensi.tinjau', $presensi), ['KEPUTUSAN' => 'HADIR'])
            ->assertRedirect();

        $this->assertSame('HADIR', $presensi->fresh()->STATUSPRESENSI);
        $this->assertSame($pengguna->IDPENGGUNA, $presensi->fresh()->IDPENINJAU);
    }

    private function buatJadwal(?Dosen $dosen = null, string $mode = 'WAJIBKELAS'): Jadwal
    {
        $dosen ??= Dosen::query()->firstOrFail();

        return Jadwal::query()->create([
            'IDDOSEN' => $dosen->IDDOSEN,
            'KODEMATAKULIAH' => 'TEST'.Str::upper(Str::random(8)),
            'NAMAMATAKULIAH' => 'Uji Presensi Cerdas',
            'SKS' => 2,
            'KELAS' => 'UJI',
            'HARI' => 'SENIN',
            'JAMMULAI' => '08:00:00',
            'JAMSELESAI' => '10:00:00',
            'RUANGAN' => 'Ruang Uji',
            'LINTANG' => -6.2,
            'BUJUR' => 106.8166667,
            'RADIUSMETER' => 100,
            'MODEKEHADIRAN' => $mode,
        ]);
    }
}
