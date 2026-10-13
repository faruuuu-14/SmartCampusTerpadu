<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ==========================================
// PEMBAHASAN / BAGIAN: Migrasi Tabel PENGGUNA
// ==========================================
return new class extends Migration
{
    // ==========================================
    // PEMBAHASAN / BAGIAN: Menjalankan Migrasi
    // ==========================================
    public function up(): void
    {
        Schema::create('PENGGUNA', function (Blueprint $table) {
            $table->id('IDPENGGUNA');
            $table->string('USERNAME')->unique();
            $table->string('PASSWORD');
            $table->string('NAMALENGKAP');
            $table->string('EMAIL')->unique()->nullable();
            $table->string('NOMORTELEPON')->unique()->nullable();
            $table->timestamp('TANGGALBINDING')->nullable();
            $table->boolean('ADALAHBAAK')->default(false);
            $table->enum('STATUSAKUN', ['AKTIF', 'NONAKTIF'])->default('AKTIF');
            $table->text('KUNCIPUBLIK')->nullable();
            $table->text('KUNCIPRIVAT')->nullable();
            $table->timestamp('TANGGALBUAT')->nullable();
            $table->timestamp('TANGGALUBAH')->nullable();
        });
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Membatalkan Migrasi
    // ==========================================
    public function down(): void
    {
        Schema::dropIfExists('PENGGUNA');
    }
};
