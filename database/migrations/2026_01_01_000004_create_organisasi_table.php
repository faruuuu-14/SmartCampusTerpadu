<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ==========================================
// PEMBAHASAN / BAGIAN: Migrasi Tabel ORGANISASI
// ==========================================
return new class extends Migration
{
    // ==========================================
    // PEMBAHASAN / BAGIAN: Menjalankan Migrasi
    // ==========================================
    public function up(): void
    {
        Schema::create('ORGANISASI', function (Blueprint $table) {
            $table->id('IDORGANISASI');
            $table->string('NAMAORGANISASI');
            $table->enum('JENISORGANISASI', ['BEM', 'HMJ', 'UKM']);
            $table->unsignedBigInteger('IDKETUA');
            $table->unsignedBigInteger('IDPEMBINA');
            $table->string('PERIODE');
            $table->enum('STATUSORGANISASI', ['AKTIF', 'NONAKTIF'])->default('AKTIF');
            $table->timestamp('TANGGALBUAT')->nullable();
            $table->timestamp('TANGGALUBAH')->nullable();

            $table->foreign('IDKETUA')->references('IDMAHASISWA')->on('MAHASISWA')->onDelete('cascade');
            $table->foreign('IDPEMBINA')->references('IDDOSEN')->on('DOSEN')->onDelete('cascade');
        });
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Membatalkan Migrasi
    // ==========================================
    public function down(): void
    {
        Schema::dropIfExists('ORGANISASI');
    }
};
