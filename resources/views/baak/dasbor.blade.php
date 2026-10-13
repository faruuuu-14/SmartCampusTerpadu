<!-- ========================================== -->
<!-- PEMBAHASAN / BAGIAN: Dasbor Ringkas BAAK -->
<!-- ========================================== -->
@extends('layout.app')

@section('judul', 'Dasbor BAAK')

@section('konten')
    <!-- ========================================== -->
    <!-- PEMBAHASAN / BAGIAN: Profil Petugas BAAK -->
    <!-- ========================================== -->
    <section class="kartu mb-4">
        <span class="lencana-peran">Peran Aktif: BAAK</span>
        <h1 class="h3 mt-3 mb-1">Portal BAAK</h1>
        <p class="mb-0 text-secondary">Petugas: {{ $pengguna->NAMALENGKAP }}</p>
    </section>

    <!-- ========================================== -->
    <!-- PEMBAHASAN / BAGIAN: Statistik Administrasi Akademik -->
    <!-- ========================================== -->
    <section class="row g-3" aria-label="Ringkasan Administrasi BAAK">
        <div class="col-12 col-md-4">
            <article class="kartu h-100">
                <h2 class="h6 text-secondary">Surat Menunggu Verifikasi</h2>
                <p class="display-6 fw-bold mb-0">{{ $jumlahSuratMenunggu }}</p>
            </article>
        </div>
        <div class="col-12 col-md-4">
            <article class="kartu h-100">
                <h2 class="h6 text-secondary">Mahasiswa Terdaftar</h2>
                <p class="display-6 fw-bold mb-0">{{ $jumlahMahasiswa }}</p>
            </article>
        </div>
        <div class="col-12 col-md-4">
            <article class="kartu h-100">
                <h2 class="h6 text-secondary">Dosen Terdaftar</h2>
                <p class="display-6 fw-bold mb-0">{{ $jumlahDosen }}</p>
            </article>
        </div>
    </section>
@endsection
