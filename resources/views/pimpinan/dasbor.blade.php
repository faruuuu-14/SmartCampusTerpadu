<!-- ========================================== -->
<!-- PEMBAHASAN / BAGIAN: Dasbor Ringkas Pimpinan -->
<!-- ========================================== -->
@extends('layout.app')

@section('judul', 'Dasbor Pimpinan')

@section('konten')
    <!-- ========================================== -->
    <!-- PEMBAHASAN / BAGIAN: Profil Struktural Pimpinan -->
    <!-- ========================================== -->
    <section class="kartu mb-4">
        <span class="lencana-peran">Peran Aktif: PIMPINAN</span>
        <h1 class="h3 mt-3 mb-1">Portal Pimpinan: {{ $pengguna->NAMALENGKAP }}</h1>
        <p class="mb-0 text-secondary">
            {{ $dosen?->JABATAN ?? 'Pimpinan' }} &middot;
            Level persetujuan {{ $dosen?->LEVELPERSETUJUAN ?? 1 }}
        </p>
    </section>

    <!-- ========================================== -->
    <!-- PEMBAHASAN / BAGIAN: Ringkasan Persetujuan Surat -->
    <!-- ========================================== -->
    <section class="row g-3" aria-label="Ringkasan Persetujuan">
        <div class="col-12 col-md-6">
            <article class="kartu h-100">
                <h2 class="h6 text-secondary">Persetujuan Menunggu</h2>
                <p class="display-6 fw-bold mb-0">{{ $jumlahPersetujuanMenunggu }} surat</p>
            </article>
        </div>
        <div class="col-12 col-md-6">
            <article class="kartu h-100">
                <h2 class="h6 text-secondary">Persetujuan Selesai</h2>
                <p class="display-6 fw-bold mb-0">{{ $jumlahPersetujuanDisetujui }} surat</p>
            </article>
        </div>
    </section>
@endsection
