<?php

namespace Tests\Feature;

use Tests\TestCase;

// ==========================================
// PEMBAHASAN / BAGIAN: Pengujian Dasar Respon Aplikasi
// ==========================================
class ExampleTest extends TestCase
{
    // ==========================================
    // PEMBAHASAN / BAGIAN: Pengujian Akses Root Dialihkan ke Login
    // ==========================================
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/login');
    }
}
