<?php

// ==========================================
// PEMBAHASAN / BAGIAN: Konfigurasi Integrasi Bot WhatsApp
// ==========================================
return [
    'mode' => env('WHATSAPP_MODE', 'log'),
    'verify_token' => env('WHATSAPP_VERIFY_TOKEN', ''),
    'app_secret' => env('WHATSAPP_APP_SECRET', ''),
    'access_token' => env('WHATSAPP_ACCESS_TOKEN', ''),
    'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID', ''),
    'api_base_url' => env('WHATSAPP_API_BASE_URL', 'https://graph.facebook.com'),
    'api_version' => env('WHATSAPP_API_VERSION', 'v23.0'),
    'api_timeout' => (int) env('WHATSAPP_API_TIMEOUT', 15),
];
