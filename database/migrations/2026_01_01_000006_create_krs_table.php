<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ==========================================
// PEMBAHASAN / BAGIAN: Migrasi Tabel KRS
// ==========================================
return new class extends Migration
{
    // ==========================================
    // PEMBAHASAN / BAGIAN: Menjalankan Migrasi
    // ==========================================
    public function up(): void
    {
        Schema::create('KRS', function (Blueprint $table) {
            $table->id('IDKRS');
            $table->unsignedBigInteger('IDJADWAL');
            $table->unsignedBigInteger('IDMAHASISWA');
            $table->timestamp('TANGGALBUAT')->nullable();
            $table->timestamp('TANGGALUBAH')->nullable();

            $table->unique(['IDJADWAL', 'IDMAHASISWA']);
            $table->foreign('IDJADWAL')->references('IDJADWAL')->on('JADWAL')->onDelete('cascade');
            $table->foreign('IDMAHASISWA')->references('IDMAHASISWA')->on('MAHASISWA')->onDelete('cascade');
        });
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Membatalkan Migrasi
    // ==========================================
    public function down(): void
    {
        Schema::dropIfExists('KRS');
    }
};
