<!-- ========================================== -->
<!-- PEMBAHASAN / BAGIAN: Tampilan Kode Pengikatan Akun WhatsApp -->
<!-- ========================================== -->
@extends('layout.app')

@section('judul', 'Ikat Akun WhatsApp')

@section('konten')
    <!-- ========================================== -->
    <!-- PEMBAHASAN / BAGIAN: Instruksi Pengikatan Sekali Pakai -->
    <!-- ========================================== -->
    <section class="kartu">
        <h1 class="h3">Ikat Akun WhatsApp</h1>
        @if ($nomorTerikat)
            <p>Nomor yang saat ini terikat: <strong>{{ $nomorTerikat }}</strong></p>
        @endif
        <p>Kirim pesan berikut dari nomor WhatsApp yang ingin diikat:</p>
        <p class="fs-4"><code>BIND {{ $kode }}</code></p>
        <p class="text-secondary mb-0">Kode sekali pakai ini berlaku selama 10 menit. Jangan bagikan kode ini kepada orang lain.</p>
    </section>
@endsection
