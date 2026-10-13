<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ==========================================
// PEMBAHASAN / BAGIAN: Migrasi Tabel PERTEMUAN
// ==========================================
return new class extends Migration
{
    // ==========================================
    // PEMBAHASAN / BAGIAN: Menjalankan Migrasi
    // ==========================================
    public function up(): void
    {
        Schema::create('PERTEMUAN', function (Blueprint $table) {
            $table->id('IDPERTEMUAN');
            $table->unsignedBigInteger('IDJADWAL');
            $table->integer('PERTEMUANKE');
            $table->date('TANGGALPERTEMUAN');
            $table->enum('STATUSPERTEMUAN', ['DIJADWALKAN', 'BERLANGSUNG', 'SELESAI', 'DIBATALKAN'])->default('DIJADWALKAN');
            $table->enum('MODEKEHADIRAN', ['WAJIBKELAS', 'BEBAS'])->nullable();
            $table->dateTime('BUKAPRESENSI')->nullable();
            $table->dateTime('TUTUPPRESENSI')->nullable();
            $table->string('TOKENSESI')->nullable();
            $table->text('ALASANBATAL')->nullable();
            $table->enum('DIBATALKANVIA', ['WEB', 'BOT'])->nullable();
            $table->string('JUDULMATERI')->nullable();
            $table->string('FILEMATERI')->nullable();
            $table->timestamp('TANGGALBUAT')->nullable();
            $table->timestamp('TANGGALUBAH')->nullable();

            $table->unique(['IDJADWAL', 'PERTEMUANKE']);
            $table->foreign('IDJADWAL')->references('IDJADWAL')->on('JADWAL')->onDelete('cascade');
        });
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Membatalkan Migrasi
    // ==========================================
    public function down(): void
    {
        Schema::dropIfExists('PERTEMUAN');
    }
};
