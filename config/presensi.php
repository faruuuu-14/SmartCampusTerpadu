<?php

// ==========================================
// PEMBAHASAN / BAGIAN: Konfigurasi Geofence, Ambang Evaluasi, dan Risiko Presensi
// ==========================================
$ambangWajah = env('WAJAHAMBANG');
$ambangWajah = $ambangWajah === null || $ambangWajah === ''
    ? 0.85
    : (float) $ambangWajah;

// ==========================================
// PEMBAHASAN / BAGIAN: Konfigurasi Aturan Presensi Cerdas
// ==========================================
return [
    'durasi_menit' => (int) env('PRESENSI_DURASI_MENIT', 180),
    'akurasi_maksimum_meter' => (float) env('PRESENSI_AKURASI_MAKSIMUM_METER', 100),
    'pengali_radius_bebas' => (float) env('PRESENSI_PENGALI_RADIUS_BEBAS', 3),
    'ambang_wajah_hadir' => $ambangWajah,
    'ambang_wajah_tolak' => $ambangWajah,
    'ambang_risiko_review' => (int) env('PRESENSI_AMBANG_RISIKO_REVIEW', 50),
    'penilai_wajah' => [
        'mode' => env('PENILAI_WAJAH_MODE', 'stub'),
        'skor_tiruan' => (float) env('PENILAI_WAJAH_SKOR_STUB', 0.93),
        'url' => env('PENILAI_WAJAH_URL'),
        'token' => env('PENILAI_WAJAH_TOKEN'),
        'timeout_detik' => (int) env('PENILAI_WAJAH_TIMEOUT_DETIK', 10),
    ],
];
