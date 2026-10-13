<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ==========================================
// PEMBAHASAN / BAGIAN: Migrasi Tabel MAHASISWA
// ==========================================
return new class extends Migration
{
    // ==========================================
    // PEMBAHASAN / BAGIAN: Menjalankan Migrasi
    // ==========================================
    public function up(): void
    {
        Schema::create('MAHASISWA', function (Blueprint $table) {
            $table->id('IDMAHASISWA');
            $table->unsignedBigInteger('IDPENGGUNA')->unique();
            $table->string('NIM')->unique();
            $table->string('NAMAMAHASISWA');
            $table->string('PROGRAMSTUDI');
            $table->string('ANGKATAN');
            $table->unsignedBigInteger('IDDOSENPA')->nullable();
            $table->binary('EMBEDDINGWAJAH')->nullable();
            $table->string('FOTOREFERENSI')->nullable();
            $table->timestamp('TANGGALKONSENBIOMETRIK')->nullable();
            $table->timestamp('TANGGALBUAT')->nullable();
            $table->timestamp('TANGGALUBAH')->nullable();

            $table->foreign('IDPENGGUNA')->references('IDPENGGUNA')->on('PENGGUNA')->onDelete('cascade');
            $table->foreign('IDDOSENPA')->references('IDDOSEN')->on('DOSEN')->onDelete('set null');
        });
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Membatalkan Migrasi
    // ==========================================
    public function down(): void
    {
        Schema::dropIfExists('MAHASISWA');
    }
};
