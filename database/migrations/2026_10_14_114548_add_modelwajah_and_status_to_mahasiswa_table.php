<?php

// ==========================================
// PEMBAHASAN / BAGIAN: Status Enrollment dan Model Wajah MAHASISWA
// ==========================================

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('MAHASISWA', function (Blueprint $table) {
            $table->string('MODELWAJAH', 30)->nullable();
            $table->string('STATUSWAJAH', 30)->default('BELUM');
            $table->unsignedBigInteger('IDVERIFIKASIBIOMETRIK')->nullable();
            $table->dateTime('WAKTUTINJAUBIOMETRIK')->nullable();
            $table->foreign('IDVERIFIKASIBIOMETRIK')
                ->references('IDPENGGUNA')
                ->on('PENGGUNA')
                ->nullOnDelete();
        });

        Schema::table('PRESENSI', function (Blueprint $table) {
            $table->string('ALASANWAJAH', 60)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('MAHASISWA', function (Blueprint $table) {
            $table->dropForeign(['IDVERIFIKASIBIOMETRIK']);
            $table->dropColumn([
                'MODELWAJAH',
                'STATUSWAJAH',
                'IDVERIFIKASIBIOMETRIK',
                'WAKTUTINJAUBIOMETRIK',
            ]);
        });

        Schema::table('PRESENSI', function (Blueprint $table) {
            $table->dropColumn('ALASANWAJAH');
        });
    }
};
