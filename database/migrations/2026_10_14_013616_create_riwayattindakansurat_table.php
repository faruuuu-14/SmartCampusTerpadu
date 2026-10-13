<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ==========================================
// PEMBAHASAN / BAGIAN: Jejak Audit Tindakan Administrasi Surat
// ==========================================
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('RIWAYATTINDAKANSURAT', function (Blueprint $table) {
            $table->id('IDRIWAYAT');
            $table->unsignedBigInteger('IDADMINISTRASI');
            $table->unsignedBigInteger('IDPELAKU');
            $table->string('TINDAKAN', 40);
            $table->text('CATATAN')->nullable();
            $table->dateTime('WAKTU');
            $table->index(['IDADMINISTRASI', 'WAKTU']);
            $table->foreign('IDADMINISTRASI')->references('IDADMINISTRASI')->on('ADMINISTRASI')->onDelete('cascade');
            $table->foreign('IDPELAKU')->references('IDPENGGUNA')->on('PENGGUNA')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('RIWAYATTINDAKANSURAT');
    }
};
