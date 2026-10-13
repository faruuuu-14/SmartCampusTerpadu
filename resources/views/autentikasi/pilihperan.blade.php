<!-- ========================================== -->
<!-- PEMBAHASAN / BAGIAN: Halaman Pemilihan Peran Aktif -->
<!-- ========================================== -->
@extends('layout.app')

@section('judul', 'Pilih Peran Aktif')

@section('konten')
    <!-- ========================================== -->
    <!-- PEMBAHASAN / BAGIAN: Ringkasan Akun dan Opsi Portal -->
    <!-- ========================================== -->
    <div class="mx-auto" style="max-width: 920px">
        <header class="text-center mb-4">
            <span class="lencana-peran">AKUN MULTI-PERAN</span>
            <h1 class="judul-halaman mt-3">Pilih portal kerja</h1>
            <p class="teks-bantu mt-2">Halo, <strong>{{ $pengguna->NAMALENGKAP }}</strong>. Pilih peran untuk melanjutkan.</p>
        </header>

        @if ($errors->any())
            <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
        @endif

        <!-- ========================================== -->
        <!-- PEMBAHASAN / BAGIAN: Kartu Pilihan Peran -->
        <!-- ========================================== -->
        <div class="row g-3">
            @foreach ($daftarPeran as $peran)
                @php
                    $detailPeran = match ($peran) {
                        'DOSEN' => ['judul' => 'Dosen', 'deskripsi' => 'Kelola pertemuan, presensi, tugas, dan penilaian.'],
                        'PIMPINAN' => ['judul' => 'Pimpinan Kampus', 'deskripsi' => 'Tinjau surat dan berikan persetujuan TTE.'],
                        'MAHASISWA' => ['judul' => 'Mahasiswa', 'deskripsi' => 'Lihat aktivitas kuliah, kumpulkan tugas, dan ajukan surat.'],
                        'ORMAWA' => ['judul' => 'Pengurus Ormawa', 'deskripsi' => 'Akses layanan organisasi dan administrasi kampus.'],
                        'BAAK' => ['judul' => 'Petugas BAAK', 'deskripsi' => 'Kelola data pengguna dan verifikasi surat.'],
                        default => ['judul' => $peran, 'deskripsi' => 'Akses portal sesuai hak Anda.'],
                    };
                @endphp
                <div class="col-12 col-md-6">
                    <article class="kartu h-100 d-flex flex-column">
                        <div class="mb-4">
                            <span class="logo-portal mb-3" aria-hidden="true">{{ mb_substr($detailPeran['judul'], 0, 1) }}</span>
                            <h2 class="h5 fw-bold">{{ $detailPeran['judul'] }}</h2>
                            <p class="teks-bantu mb-0">{{ $detailPeran['deskripsi'] }}</p>
                        </div>
                        <form class="mt-auto" action="{{ route('autentikasi.pilihperan.simpan') }}" method="POST">
                            @csrf
                            <input type="hidden" name="PERAN" value="{{ $peran }}">
                            <button type="submit" class="btn btn-primary w-100">Masuk sebagai {{ $detailPeran['judul'] }}</button>
                        </form>
                    </article>
                </div>
            @endforeach
        </div>
    </div>
@endsection
