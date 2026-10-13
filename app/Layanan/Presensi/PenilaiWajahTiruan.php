<?php

namespace App\Layanan\Presensi;

use App\Models\Mahasiswa;
use Illuminate\Http\UploadedFile;

// ==========================================
// PEMBAHASAN / BAGIAN: Penilai Wajah Tiruan untuk Demo
// ==========================================
class PenilaiWajahTiruan implements PenilaiWajah
{
    public function nilai(UploadedFile $foto, Mahasiswa $mahasiswa): float
    {
        return (float) config('presensi.penilai_wajah.skor_tiruan');
    }
}
