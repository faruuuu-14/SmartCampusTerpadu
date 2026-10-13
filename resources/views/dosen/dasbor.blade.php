<!-- ========================================== -->
<!-- PEMBAHASAN / BAGIAN: Dasbor Ringkas Dosen -->
<!-- ========================================== -->
@extends('layout.app')

@section('judul', 'Dasbor Dosen')

@section('konten')
    <!-- ========================================== -->
    <!-- PEMBAHASAN / BAGIAN: Profil Dosen -->
    <!-- ========================================== -->
    <section class="kartu mb-4">
        <span class="lencana-peran">Peran Aktif: DOSEN</span>
        <h1 class="h3 mt-3 mb-1">Selamat datang, {{ $pengguna->NAMALENGKAP }}</h1>
        <p class="mb-0 text-secondary">
            NIDN {{ $dosen?->NIDN ?? '-' }} &middot; {{ $dosen?->PROGRAMSTUDI ?? '-' }}
        </p>
    </section>

    <!-- ========================================== -->
    <!-- PEMBAHASAN / BAGIAN: Ringkasan Aktivitas Mengajar -->
    <!-- ========================================== -->
    <section class="row g-3" aria-label="Ringkasan Aktivitas Dosen">
        <div class="col-12 col-md-6">
            <article class="kartu h-100">
                <h2 class="h6 text-secondary">Jadwal Diampu</h2>
                <p class="display-6 fw-bold mb-0">{{ $jumlahJadwalDiampu }} kelas</p>
            </article>
        </div>
        <div class="col-12 col-md-6">
            <article class="kartu h-100">
                <h2 class="h6 text-secondary">Mahasiswa Bimbingan</h2>
                <p class="display-6 fw-bold mb-0">{{ $jumlahMahasiswaBimbingan }} mahasiswa</p>
            </article>
        </div>
    </section>
@endsection
