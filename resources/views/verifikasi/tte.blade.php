<!-- ========================================== -->
<!-- PEMBAHASAN / BAGIAN: Halaman Verifikasi Publik TTE -->
<!-- ========================================== -->
@extends('layout.app')

@section('judul', 'Verifikasi Tanda Tangan Elektronik')

@section('konten')
    <!-- ========================================== -->
    <!-- PEMBAHASAN / BAGIAN: Status Keabsahan Tanda Tangan -->
    <!-- ========================================== -->
    <section class="kartu">
        <h1 class="h3">Verifikasi Tanda Tangan Elektronik</h1>
        <p class="mb-2">Nomor surat: <strong>{{ $surat?->NOMORSURAT ?? 'Belum diterbitkan' }}</strong></p>
        <p class="mb-2">Penandatangan: {{ $persetujuan->penyetuju?->NAMALENGKAP }} &middot; {{ $persetujuan->penyetuju?->dosen?->JABATAN }}</p>
        <p class="mb-3">Waktu tanda tangan: {{ $persetujuan->WAKTUKEPUTUSAN?->format('d/m/Y H:i:s') ?? '-' }}</p>

        <div class="alert {{ $validTandaTangan ? 'alert-success' : 'alert-danger' }}">
            Tanda tangan dan hash isi:
            <strong>{{ $validTandaTangan ? 'VALID' : 'TIDAK VALID' }}</strong>
        </div>
        @if ($surat?->STATUSADMINISTRASI === 'TERBIT')
            <div class="alert {{ $validBerkas ? 'alert-success' : 'alert-danger' }}">
                Integritas PDF final:
                <strong>{{ $validBerkas ? 'VALID' : 'TIDAK VALID / BERKAS TIDAK TERSEDIA' }}</strong>
            </div>
            @if ($validBerkas && $validTandaTangan)
                <a class="btn btn-primary" href="{{ route('tte.unduh', $persetujuan->TOKENVERIFIKASI) }}">Unduh PDF terverifikasi</a>
            @endif
        @else
            <p class="text-secondary">PDF final belum diterbitkan.</p>
        @endif
        <p class="small text-secondary text-break mt-3 mb-0">Sidik jari kunci publik: {{ $persetujuan->SIDIKJARIKUNCI }}</p>
    </section>
@endsection
