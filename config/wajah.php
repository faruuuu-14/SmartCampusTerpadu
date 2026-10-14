<?php

// ==========================================
// PEMBAHASAN / BAGIAN: Konfigurasi Layanan, Ambang, dan Retensi Biometrik
// ==========================================
$ambang = env('WAJAHAMBANG');
$retensiHari = env('WAJAH_RETENSI_HARI');

return [
    'mode' => env('WAJAH_MODE', 'layanan'),
    'url' => env('WAJAH_URL', 'http://127.0.0.1:8765'),
    'kunci' => env('WAJAH_KUNCI', ''),
    'model' => env('WAJAH_MODEL', 'FaceNet'),
    'ambang' => $ambang === null || $ambang === '' ? null : (float) $ambang,
    'skor_di_bawah_ambang' => env('WAJAH_SKOR_TIDAK_COCOK', 'review'),
    'retensi_hari' => $retensiHari === null || $retensiHari === '' ? null : (int) $retensiHari,
];
