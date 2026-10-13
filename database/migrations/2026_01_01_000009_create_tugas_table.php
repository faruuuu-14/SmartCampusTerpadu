<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ==========================================
// PEMBAHASAN / BAGIAN: Migrasi Tabel TUGAS
// ==========================================
return new class extends Migration
{
    // ==========================================
    // PEMBAHASAN / BAGIAN: Menjalankan Migrasi
    // ==========================================
    public function up(): void
    {
        Schema::create('TUGAS', function (Blueprint $table) {
            $table->id('IDTUGAS');
            $table->unsignedBigInteger('IDJADWAL');
            $table->unsignedBigInteger('IDDOSEN');
            $table->string('JUDULTUGAS');
            $table->text('DESKRIPSI')->nullable();
            $table->string('FILETUGAS')->nullable();
            $table->dateTime('TANGGALTENGGAT');
            $table->enum('MODEPENGERJAAN', ['BEBAS', 'WAJIBKELAS']);
            $table->enum('DIBUATVIA', ['WEB', 'BOT']);
            $table->dateTime('TANGGALSIARAN')->nullable();
            $table->timestamp('TANGGALBUAT')->nullable();
            $table->timestamp('TANGGALUBAH')->nullable();

            $table->foreign('IDJADWAL')->references('IDJADWAL')->on('JADWAL')->onDelete('cascade');
            $table->foreign('IDDOSEN')->references('IDDOSEN')->on('DOSEN')->onDelete('cascade');
        });
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Membatalkan Migrasi
    // ==========================================
    public function down(): void
    {
        Schema::dropIfExists('TUGAS');
    }
};
