<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ==========================================
// PEMBAHASAN / BAGIAN: Migrasi Antrean Pengingat Kuliah
// ==========================================
return new class extends Migration
{
    // ==========================================
    // PEMBAHASAN / BAGIAN: Membuat Antrean Idempoten per Jadwal Mahasiswa
    // ==========================================
    public function up(): void
    {
        Schema::create('ANTRIANPENGINGAT', function (Blueprint $table) {
            $table->id('IDANTRIANPENGINGAT');
            $table->unsignedBigInteger('IDJADWAL');
            $table->unsignedBigInteger('IDMAHASISWA');
            $table->dateTime('WAKTUKULIAH');
            $table->dateTime('WAKTUKIRIM');
            $table->enum('STATUSPENGINGAT', ['MENUNGGU', 'TERKIRIM', 'GAGAL'])->default('MENUNGGU');
            $table->dateTime('TANGGALKIRIM')->nullable();
            $table->string('CATATAN')->nullable();
            $table->timestamp('TANGGALBUAT')->nullable();
            $table->timestamp('TANGGALUBAH')->nullable();

            $table->unique(['IDJADWAL', 'IDMAHASISWA', 'WAKTUKULIAH']);
            $table->index(['STATUSPENGINGAT', 'WAKTUKIRIM']);
            $table->foreign('IDJADWAL')->references('IDJADWAL')->on('JADWAL')->onDelete('cascade');
            $table->foreign('IDMAHASISWA')->references('IDMAHASISWA')->on('MAHASISWA')->onDelete('cascade');
        });
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Menghapus Antrean Pengingat
    // ==========================================
    public function down(): void
    {
        Schema::dropIfExists('ANTRIANPENGINGAT');
    }
};
