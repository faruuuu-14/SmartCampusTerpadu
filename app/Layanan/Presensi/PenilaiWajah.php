<?php

namespace App\Layanan\Presensi;

use App\Models\Mahasiswa;
use Illuminate\Http\UploadedFile;

// ==========================================
// PEMBAHASAN / BAGIAN: Kontrak Penilaian Wajah
// ==========================================
interface PenilaiWajah
{
    public function nilai(UploadedFile $foto, Mahasiswa $mahasiswa): float;
}
