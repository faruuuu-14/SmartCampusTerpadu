<?php

namespace Tests\Feature;

use Tests\TestCase;

// ==========================================
// PEMBAHASAN / BAGIAN: Pengujian Fitur Autentikasi & Deteksi Peran Multi-Role
// ==========================================
class AutentikasiTest extends TestCase
{
    // ==========================================
    // PEMBAHASAN / BAGIAN: Pengujian Pengalihan Halaman Tamu ke Form Login
    // ==========================================
    public function test_tamu_dialihkan_ke_halaman_login(): void
    {
        $response = $this->get('/');
        $response->assertRedirect('/login');

        $responseLogin = $this->get('/login');
        $responseLogin->assertStatus(200);
        $responseLogin->assertSee('Satu Pintu Masuk');
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Pengujian Login dengan Kredensial Salah
    // ==========================================
    public function test_login_gagal_dengan_sandi_salah(): void
    {
        $response = $this->post('/login', [
            'USERNAME' => 'BAAK001',
            'PASSWORD' => 'SandiSalah123',
        ]);

        $response->assertSessionHasErrors('USERNAME');
        $this->assertGuest();
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Pengujian Login Akun Peran Tunggal (BAAK)
    // ==========================================
    public function test_login_akun_peran_tunggal_langsung_ke_dasbor(): void
    {
        $response = $this->post('/login', [
            'USERNAME' => 'BAAK001',
            'PASSWORD' => 'SmartCampus2026!',
        ]);

        $response->assertRedirect(route('baak.dasbor'));
        $this->assertEquals('BAAK', session('PERANAKTIF'));

        $responseDasbor = $this->get(route('baak.dasbor'));
        $responseDasbor->assertStatus(200);
        $responseDasbor->assertSee('Portal BAAK');
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Pengujian Login Akun Multi-Role (Pimpinan / Dosen)
    // ==========================================
    public function test_login_akun_multi_role_diarahkan_ke_pilih_peran(): void
    {
        $response = $this->post('/login', [
            'USERNAME' => 'DOSEN001',
            'PASSWORD' => 'SmartCampus2026!',
        ]);

        $response->assertRedirect(route('autentikasi.pilihperan'));

        $responsePilih = $this->get(route('autentikasi.pilihperan'));
        $responsePilih->assertStatus(200);
        $responsePilih->assertSee('Pilih Peran Masuk');
        $responsePilih->assertSee('PIMPINAN');
        $responsePilih->assertSee('DOSEN');

        // Pilih peran PIMPINAN
        $responseSimpan = $this->post(route('autentikasi.pilihperan.simpan'), [
            'PERAN' => 'PIMPINAN',
        ]);

        $responseSimpan->assertRedirect(route('pimpinan.dasbor'));
        $this->assertEquals('PIMPINAN', session('PERANAKTIF'));

        // Akses dasbor pimpinan berhasil
        $responsePimpinan = $this->get(route('pimpinan.dasbor'));
        $responsePimpinan->assertStatus(200);
        $responsePimpinan->assertSee('Portal Pimpinan');
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Pengujian Penegakan Keamanan Middleware DeteksiPeran (403 Forbidden)
    // ==========================================
    public function test_middleware_menolak_akses_jika_peran_tidak_sesuai(): void
    {
        // Login sebagai BAAK
        $this->post('/login', [
            'USERNAME' => 'BAAK001',
            'PASSWORD' => 'SmartCampus2026!',
        ]);

        // Coba akses dasbor mahasiswa yang bukan hak peran BAAK
        $response = $this->get(route('mahasiswa.dasbor'));
        $response->assertStatus(403);
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Pengujian Logout Sistem
    // ==========================================
    public function test_logout_membersihkan_sesi(): void
    {
        $this->post('/login', [
            'USERNAME' => 'BAAK001',
            'PASSWORD' => 'SmartCampus2026!',
        ]);

        $this->assertAuthenticated();

        $responseLogout = $this->post(route('logout'));
        $responseLogout->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
