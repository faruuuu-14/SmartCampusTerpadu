<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ==========================================
// PEMBAHASAN / BAGIAN: Migrasi Tabel PRESENSI
// ==========================================
return new class extends Migration
{
    // ==========================================
    // PEMBAHASAN / BAGIAN: Menjalankan Migrasi
    // ==========================================
    public function up(): void
    {
        Schema::create('PRESENSI', function (Blueprint $table) {
            $table->id('IDPRESENSI');
            $table->unsignedBigInteger('IDPERTEMUAN');
            $table->unsignedBigInteger('IDMAHASISWA');
            $table->dateTime('WAKTUKIRIM');
            $table->string('FILEFOTO')->nullable();
            $table->decimal('LINTANG', 10, 7)->nullable();
            $table->decimal('BUJUR', 10, 7)->nullable();
            $table->decimal('AKURASIGPS', 8, 2)->nullable();
            $table->decimal('JARAKMETER', 8, 2)->nullable();
            $table->decimal('SKORWAJAH', 5, 4)->nullable();
            $table->decimal('SKORLINGKUNGAN', 5, 4)->nullable();
            $table->boolean('LIVENESSLOLOS')->nullable();
            $table->tinyInteger('SKORRISIKO')->default(0);
            $table->enum('STATUSPRESENSI', ['HADIR', 'PERLUREVIEW', 'DITOLAK', 'IZIN', 'SAKIT', 'ALPHA']);
            $table->unsignedBigInteger('IDPENINJAU')->nullable();
            $table->string('SIDIKJARIPERANGKAT')->nullable();
            $table->string('IDPESANWA')->unique()->nullable();
            $table->timestamp('TANGGALBUAT')->nullable();
            $table->timestamp('TANGGALUBAH')->nullable();

            $table->unique(['IDPERTEMUAN', 'IDMAHASISWA']);
            $table->foreign('IDPERTEMUAN')->references('IDPERTEMUAN')->on('PERTEMUAN')->onDelete('cascade');
            $table->foreign('IDMAHASISWA')->references('IDMAHASISWA')->on('MAHASISWA')->onDelete('cascade');
            $table->foreign('IDPENINJAU')->references('IDPENGGUNA')->on('PENGGUNA')->onDelete('set null');
        });
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Membatalkan Migrasi
    // ==========================================
    public function down(): void
    {
        Schema::dropIfExists('PRESENSI');
    }
};
