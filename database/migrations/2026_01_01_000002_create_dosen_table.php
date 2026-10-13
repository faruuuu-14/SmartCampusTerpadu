<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ==========================================
// PEMBAHASAN / BAGIAN: Migrasi Tabel DOSEN
// ==========================================
return new class extends Migration
{
    // ==========================================
    // PEMBAHASAN / BAGIAN: Menjalankan Migrasi
    // ==========================================
    public function up(): void
    {
        Schema::create('DOSEN', function (Blueprint $table) {
            $table->id('IDDOSEN');
            $table->unsignedBigInteger('IDPENGGUNA')->unique();
            $table->string('NIDN')->unique();
            $table->string('NIP')->nullable();
            $table->string('NAMADOSEN');
            $table->string('PROGRAMSTUDI');
            $table->string('JABATAN')->nullable();
            $table->tinyInteger('LEVELPERSETUJUAN')->nullable();
            $table->timestamp('TANGGALBUAT')->nullable();
            $table->timestamp('TANGGALUBAH')->nullable();

            $table->foreign('IDPENGGUNA')->references('IDPENGGUNA')->on('PENGGUNA')->onDelete('cascade');
        });
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Membatalkan Migrasi
    // ==========================================
    public function down(): void
    {
        Schema::dropIfExists('DOSEN');
    }
};
