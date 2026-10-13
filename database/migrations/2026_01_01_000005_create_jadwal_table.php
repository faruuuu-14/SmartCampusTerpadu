<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ==========================================
// PEMBAHASAN / BAGIAN: Migrasi Tabel JADWAL
// ==========================================
return new class extends Migration
{
    // ==========================================
    // PEMBAHASAN / BAGIAN: Menjalankan Migrasi
    // ==========================================
    public function up(): void
    {
        Schema::create('JADWAL', function (Blueprint $table) {
            $table->id('IDJADWAL');
            $table->unsignedBigInteger('IDDOSEN');
            $table->string('KODEMATAKULIAH');
            $table->string('NAMAMATAKULIAH');
            $table->integer('SKS');
            $table->string('KELAS');
            $table->enum('HARI', ['SENIN', 'SELASA', 'RABU', 'KAMIS', 'JUMAT', 'SABTU']);
            $table->time('JAMMULAI');
            $table->time('JAMSELESAI');
            $table->string('RUANGAN');
            $table->decimal('LINTANG', 10, 7);
            $table->decimal('BUJUR', 10, 7);
            $table->integer('RADIUSMETER')->default(100);
            $table->enum('MODEKEHADIRAN', ['WAJIBKELAS', 'BEBAS']);
            $table->integer('MENITPENGINGAT')->default(30);
            $table->timestamp('TANGGALBUAT')->nullable();
            $table->timestamp('TANGGALUBAH')->nullable();

            $table->foreign('IDDOSEN')->references('IDDOSEN')->on('DOSEN')->onDelete('cascade');
        });
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Membatalkan Migrasi
    // ==========================================
    public function down(): void
    {
        Schema::dropIfExists('JADWAL');
    }
};
