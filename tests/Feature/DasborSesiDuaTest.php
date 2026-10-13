<?php

namespace Tests\Feature;

use Illuminate\Support\Carbon;
use Tests\TestCase;

// ==========================================
// PEMBAHASAN / BAGIAN: Pengujian Dasbor dan Pembatasan Peran Sesi 2
// ==========================================
class DasborSesiDuaTest extends TestCase
{
    // ==========================================
    // PEMBAHASAN / BAGIAN: Profil, Jadwal KRS, dan Ringkasan Presensi Mahasiswa
    // ==========================================
    public function test_mahasiswa_melihat_profil_jadwal_dan_ringkasan_kehadiran(): void
    {
        $this->travelTo(Carbon::parse('2026-10-14 09:00:00'));
        $responseLogin = $this->post(route('login.proses'), [
            'USERNAME' => '230201004',
            'PASSWORD' => 'SmartCampus2026!',
        ]);
        $responseLogin->assertRedirect(route('mahasiswa.dasbor'));

        $response = $this->get(route('mahasiswa.dasbor'));

        $response->assertOk();
        $response->assertSee('Dewi Lestari');
        $response->assertSee('Basis Data Terdistribusi');
        $response->assertSee('Presensi Hadir');
        $response->assertSee('Jadwal Hari Ini');
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Profil dan Statistik Jadwal Dosen
    // ==========================================
    public function test_dosen_melihat_profil_dan_jumlah_jadwal_diampu(): void
    {
        $responseLogin = $this->post(route('login.proses'), [
            'USERNAME' => 'DOSEN002',
            'PASSWORD' => 'SmartCampus2026!',
        ]);
        $responseLogin->assertRedirect(route('dosen.dasbor'));

        $response = $this->get(route('dosen.dasbor'));

        $response->assertOk();
        $response->assertSee('Ratna Sari');
        $response->assertSee('Jadwal Diampu');
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Statistik Nyata Dasbor BAAK
    // ==========================================
    public function test_baak_melihat_antrean_surat_dan_data_induk(): void
    {
        $responseLogin = $this->post(route('login.proses'), [
            'USERNAME' => 'BAAK001',
            'PASSWORD' => 'SmartCampus2026!',
        ]);
        $responseLogin->assertRedirect(route('baak.dasbor'));

        $response = $this->get(route('baak.dasbor'));

        $response->assertOk();
        $response->assertSee('Surat Menunggu Verifikasi');
        $response->assertSee('Mahasiswa Terdaftar');
        $response->assertSee('Dosen Terdaftar');
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Statistik Persetujuan Dasbor Pimpinan
    // ==========================================
    public function test_pimpinan_melihat_ringkasan_persetujuan_surat(): void
    {
        $responseLogin = $this->post(route('login.proses'), [
            'USERNAME' => 'DOSEN001',
            'PASSWORD' => 'SmartCampus2026!',
        ]);
        $responseLogin->assertRedirect(route('autentikasi.pilihperan'));

        $responsePilihPeran = $this->post(route('autentikasi.pilihperan.simpan'), [
            'PERAN' => 'PIMPINAN',
        ]);
        $responsePilihPeran->assertRedirect(route('pimpinan.dasbor'));

        $response = $this->get(route('pimpinan.dasbor'));

        $response->assertOk();
        $response->assertSee('Portal Pimpinan');
        $response->assertSee('Persetujuan Menunggu');
        $response->assertSee('Persetujuan Selesai');
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Pengalihan Saat Peran Aktif Tidak Sesuai
    // ==========================================
    public function test_dasbor_peran_lain_mengharuskan_pemilihan_ulang(): void
    {
        $this->post(route('login.proses'), [
            'USERNAME' => 'DOSEN001',
            'PASSWORD' => 'SmartCampus2026!',
        ]);
        $this->post(route('autentikasi.pilihperan.simpan'), [
            'PERAN' => 'DOSEN',
        ]);

        $response = $this->get(route('pimpinan.dasbor'));

        $response->assertRedirect(route('autentikasi.pilihperan'));
        $response->assertSessionMissing('PERANAKTIF');
    }
}
