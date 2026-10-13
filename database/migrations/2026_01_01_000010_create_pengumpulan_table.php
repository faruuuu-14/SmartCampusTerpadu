<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ==========================================
// PEMBAHASAN / BAGIAN: Migrasi Tabel PENGUMPULAN
// ==========================================
return new class extends Migration
{
    // ==========================================
    // PEMBAHASAN / BAGIAN: Menjalankan Migrasi
    // ==========================================
    public function up(): void
    {
        Schema::create('PENGUMPULAN', function (Blueprint $table) {
            $table->id('IDPENGUMPULAN');
            $table->unsignedBigInteger('IDTUGAS');
            $table->unsignedBigInteger('IDMAHASISWA');
            $table->enum('STATUSPENGUMPULAN', ['BELUM', 'SUDAH', 'TERLAMBAT', 'DINILAI']);
            $table->string('FILEKUMPUL')->nullable();
            $table->char('HASHFILE', 64)->nullable();
            $table->dateTime('WAKTUKUMPUL')->nullable();
            $table->string('FOTOVERIFIKASI')->nullable();
            $table->decimal('SKORWAJAH', 5, 4)->nullable();
            $table->decimal('NILAI', 5, 2)->nullable();
            $table->text('UMPANBALIK')->nullable();
            $table->timestamp('TANGGALBUAT')->nullable();
            $table->timestamp('TANGGALUBAH')->nullable();

            $table->unique(['IDTUGAS', 'IDMAHASISWA']);
            $table->foreign('IDTUGAS')->references('IDTUGAS')->on('TUGAS')->onDelete('cascade');
            $table->foreign('IDMAHASISWA')->references('IDMAHASISWA')->on('MAHASISWA')->onDelete('cascade');
        });
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Membatalkan Migrasi
    // ==========================================
    public function down(): void
    {
        Schema::dropIfExists('PENGUMPULAN');
    }
};
