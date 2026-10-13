<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ==========================================
// PEMBAHASAN / BAGIAN: Migrasi Tabel ADMINISTRASI
// ==========================================
return new class extends Migration
{
    // ==========================================
    // PEMBAHASAN / BAGIAN: Menjalankan Migrasi
    // ==========================================
    public function up(): void
    {
        Schema::create('ADMINISTRASI', function (Blueprint $table) {
            $table->id('IDADMINISTRASI');
            $table->unsignedBigInteger('IDPEMOHON');
            $table->unsignedBigInteger('IDORGANISASI')->nullable();
            $table->enum('JENISSURAT', [
                'SURATAKTIF',
                'TRANSKRIP',
                'MAGANG',
                'DISPENSASI',
                'IZINSAKIT',
                'PROPOSALORMAWA',
                'LPJORMAWA',
            ]);
            $table->string('PERIHAL');
            $table->text('KEPERLUAN');
            $table->json('DATAFORMULIR')->nullable();
            $table->string('FILELAMPIRAN')->nullable();
            $table->enum('STATUSADMINISTRASI', [
                'DIAJUKAN',
                'VERIFIKASIBAAK',
                'PERLUREVISI',
                'MENUNGGUPERSETUJUAN',
                'DITOLAK',
                'TERBIT',
            ])->default('DIAJUKAN');
            $table->string('NOMORSURAT')->unique()->nullable();
            $table->longText('ISISURATKANONIK')->nullable();
            $table->char('HASHISI', 64)->nullable();
            $table->char('HASHBERKAS', 64)->nullable();
            $table->string('FILEAKHIR')->nullable();
            $table->dateTime('TANGGALTERBIT')->nullable();
            $table->text('CATATAN')->nullable();
            $table->timestamp('TANGGALBUAT')->nullable();
            $table->timestamp('TANGGALUBAH')->nullable();

            $table->foreign('IDPEMOHON')->references('IDPENGGUNA')->on('PENGGUNA')->onDelete('cascade');
            $table->foreign('IDORGANISASI')->references('IDORGANISASI')->on('ORGANISASI')->onDelete('set null');
        });
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Membatalkan Migrasi
    // ==========================================
    public function down(): void
    {
        Schema::dropIfExists('ADMINISTRASI');
    }
};
