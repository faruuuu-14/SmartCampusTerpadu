<?php

// ==========================================
// PEMBAHASAN / BAGIAN: Konfigurasi Integrasi Bot WhatsApp
// ==========================================
return [
    'mode' => env('WHATSAPP_MODE', 'log'),
    'verify_token' => env('WHATSAPP_VERIFY_TOKEN', ''),
    'app_secret' => env('WHATSAPP_APP_SECRET', ''),
];
