<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ==========================================
// PEMBAHASAN / BAGIAN: Migrasi Tabel PERSETUJUANSURAT
// ==========================================
return new class extends Migration
{
    // ==========================================
    // PEMBAHASAN / BAGIAN: Menjalankan Migrasi
    // ==========================================
    public function up(): void
    {
        Schema::create('PERSETUJUANSURAT', function (Blueprint $table) {
            $table->id('IDPERSETUJUAN');
            $table->unsignedBigInteger('IDADMINISTRASI');
            $table->unsignedBigInteger('IDPENYETUJU');
            $table->string('PERANPENYETUJU');
            $table->integer('URUTAN');
            $table->enum('STATUSPERSETUJUAN', ['MENUNGGU', 'DISETUJUI', 'DITOLAK'])->default('MENUNGGU');
            $table->text('CATATAN')->nullable();
            $table->dateTime('WAKTUKEPUTUSAN')->nullable();
            $table->text('MUATANTTD')->nullable();
            $table->text('TANDATANGAN')->nullable();
            $table->string('ALGORITMA')->default('ED25519');
            $table->char('SIDIKJARIKUNCI', 64)->nullable();
            $table->char('TOKENVERIFIKASI', 36)->unique()->nullable();
            $table->timestamp('TANGGALBUAT')->nullable();
            $table->timestamp('TANGGALUBAH')->nullable();

            $table->unique(['IDADMINISTRASI', 'URUTAN']);
            $table->foreign('IDADMINISTRASI')->references('IDADMINISTRASI')->on('ADMINISTRASI')->onDelete('cascade');
            $table->foreign('IDPENYETUJU')->references('IDPENGGUNA')->on('PENGGUNA')->onDelete('cascade');
        });
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Membatalkan Migrasi
    // ==========================================
    public function down(): void
    {
        Schema::dropIfExists('PERSETUJUANSURAT');
    }
};
