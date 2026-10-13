<?php

namespace App\Layanan\TandaTanganElektronik;

use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;

// ==========================================
// PEMBAHASAN / BAGIAN: Pembuatan QR Code PNG untuk Verifikasi
// ==========================================
class QrLayanan
{
    // ==========================================
    // PEMBAHASAN / BAGIAN: Menghasilkan QR Code sebagai Data URI
    // ==========================================
    public function dataUri(string $isi): string
    {
        $qr = new QrCode(
            data: $isi,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: 280,
            margin: 8,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
            foregroundColor: new Color(0, 0, 0),
            backgroundColor: new Color(255, 255, 255),
        );

        return 'data:image/png;base64,'.base64_encode((new PngWriter)->write($qr)->getString());
    }
}
