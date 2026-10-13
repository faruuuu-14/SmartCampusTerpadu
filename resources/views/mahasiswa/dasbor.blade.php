<!-- ========================================== -->
<!-- PEMBAHASAN / BAGIAN: Dasbor Akademik Mahasiswa -->
<!-- ========================================== -->
@extends('layout.app')

@section('judul', 'Dasbor Mahasiswa')

@section('konten')
    <!-- ========================================== -->
    <!-- PEMBAHASAN / BAGIAN: Profil Ringkas Mahasiswa -->
    <!-- ========================================== -->
    <section class="kartu mb-4">
        <span class="lencana-peran">Peran Aktif: MAHASISWA</span>
        <h1 class="h3 mt-3 mb-1">Selamat datang, {{ $pengguna->NAMALENGKAP }}</h1>
        <p class="mb-0 text-secondary">
            NIM {{ $mahasiswa?->NIM ?? '-' }} &middot;
            {{ $mahasiswa?->PROGRAMSTUDI ?? '-' }} &middot;
            Angkatan {{ $mahasiswa?->ANGKATAN ?? '-' }}
        </p>
    </section>

    <!-- ========================================== -->
    <!-- PEMBAHASAN / BAGIAN: Ringkasan Kehadiran Nyata -->
    <!-- ========================================== -->
    <section class="row g-3 mb-4" aria-label="Ringkasan Kehadiran">
        <div class="col-12 col-md-6">
            <article class="kartu h-100">
                <h2 class="h6 text-secondary">Presensi Hadir</h2>
                <p class="display-6 fw-bold mb-1">{{ $jumlahHadir }}</p>
                <p class="small text-secondary mb-0">Dari {{ $jumlahPresensi }} catatan presensi</p>
            </article>
        </div>
        <div class="col-12 col-md-6">
            <article class="kartu h-100">
                <h2 class="h6 text-secondary">Mata Kuliah Hari Ini</h2>
                <p class="display-6 fw-bold mb-1">{{ $jadwalHariIni->count() }}</p>
                <p class="small text-secondary mb-0">{{ $hariIni }}</p>
            </article>
        </div>
    </section>

    <!-- ========================================== -->
    <!-- PEMBAHASAN / BAGIAN: Jadwal Hari Ini dari KRS -->
    <!-- ========================================== -->
    <section class="kartu">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
            <h2 class="h5 mb-0">Jadwal Hari Ini</h2>
            <span class="small text-secondary">{{ $hariIni }}</span>
        </div>

        @forelse ($jadwalHariIni as $krs)
            <article class="d-flex flex-wrap justify-content-between gap-2 border-top py-3">
                <div>
                    <h3 class="h6 mb-1">{{ $krs->jadwal->NAMAMATAKULIAH }}</h3>
                    <p class="small text-secondary mb-0">{{ $krs->jadwal->KODEMATAKULIAH }} &middot; Kelas {{ $krs->jadwal->KELAS }} &middot; {{ $krs->jadwal->RUANGAN }}</p>
                </div>
                <p class="small fw-semibold mb-0">{{ $krs->jadwal->JAMMULAI }}–{{ $krs->jadwal->JAMSELESAI }}</p>
            </article>
        @empty
            <p class="border-top pt-3 text-secondary mb-0">Tidak ada jadwal kuliah pada hari ini.</p>
        @endforelse
    </section>
@endsection
