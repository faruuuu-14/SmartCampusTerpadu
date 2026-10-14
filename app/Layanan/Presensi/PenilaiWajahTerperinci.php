<?php

// ==========================================
// PEMBAHASAN / BAGIAN: Kontrak Penilai Wajah dengan Metadata Deteksi
// ==========================================

namespace App\Layanan\Presensi;

use App\Models\Mahasiswa;
use Illuminate\Http\UploadedFile;

interface PenilaiWajahTerperinci extends PenilaiWajah
{
    public function nilaiTerperinci(UploadedFile $foto, Mahasiswa $mahasiswa): HasilPenilaianWajah;
}
