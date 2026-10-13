<!-- ========================================== -->
<!-- PEMBAHASAN / BAGIAN: Templat PDF Surat Elektronik -->
<!-- ========================================== -->
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11pt; color: #172033; }
        h1 { text-align: center; font-size: 16pt; margin-bottom: 24px; }
        .isi { border-top: 1px solid #777; border-bottom: 1px solid #777; padding: 14px 0; }
        .baris { margin: 0 0 8px; }
        .tanda-tangan { width: 100%; border-collapse: collapse; margin-top: 28px; }
        .tanda-tangan td { width: 50%; text-align: center; vertical-align: top; padding: 10px; }
        .qr { width: 110px; height: 110px; }
    </style>
</head>
<body>
    <!-- ========================================== -->
    <!-- PEMBAHASAN / BAGIAN: Isi Surat yang Dibekukan -->
    <!-- ========================================== -->
    <h1>{{ $isiSurat['PERIHAL'] ?? 'Surat Keterangan' }}</h1>
    <div class="isi">
        @foreach ($isiSurat as $kunci => $nilai)
            <p class="baris"><strong>{{ $kunci }}:</strong>
                {{ is_array($nilai) ? json_encode($nilai, JSON_UNESCAPED_UNICODE) : $nilai }}
            </p>
        @endforeach
        <p class="baris">Dengan ini surat diterbitkan berdasarkan data yang dibekukan dan diverifikasi.</p>
    </div>

    <!-- ========================================== -->
    <!-- PEMBAHASAN / BAGIAN: Blok TTE dan QR Per Penandatangan -->
    <!-- ========================================== -->
    <table class="tanda-tangan">
        <tr>
            @foreach ($blokTandaTangan as $blok)
                <td>
                    <p>{{ $blok['jabatan'] }}</p>
                    <p><strong>{{ $blok['nama'] }}</strong></p>
                    <p><em>Ditandatangani secara elektronik</em></p>
                    <p>{{ $blok['waktu'] }}</p>
                    <img class="qr" src="{{ $blok['qr'] }}" alt="QR verifikasi tanda tangan">
                </td>
            @endforeach
        </tr>
    </table>
</body>
</html>
