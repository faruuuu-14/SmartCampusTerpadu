<!-- ========================================== -->
<!-- PEMBAHASAN / BAGIAN: Navigasi Responsif Sesuai Peran Aktif -->
<!-- ========================================== -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark" aria-label="Navigasi utama">
    <div class="container">
        <button class="navbar-toggler ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#navigasiPortal" aria-controls="navigasiPortal" aria-expanded="false" aria-label="Buka navigasi">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navigasiPortal">
            <ul class="navbar-nav me-auto">
                @auth
                    @switch(session('PERANAKTIF'))
                        @case('MAHASISWA')
                            <li class="nav-item"><a class="nav-link" href="{{ route('mahasiswa.dasbor') }}">Dasbor Mahasiswa</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('mahasiswa.surat.indeks') }}">Pengajuan Surat</a></li>
                            @break
                        @case('DOSEN')
                            <li class="nav-item"><a class="nav-link" href="{{ route('dosen.dasbor') }}">Dasbor Dosen</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('dosen.pertemuan.indeks') }}">Kelola Pertemuan</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('dosen.rekap.indeks') }}">Rekap Presensi</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('dosen.tugas.indeks') }}">Tugas</a></li>
                            @break
                        @case('BAAK')
                            <li class="nav-item"><a class="nav-link" href="{{ route('baak.dasbor') }}">Dasbor BAAK</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('baak.surat.indeks') }}">Verifikasi Surat</a></li>
                            @break
                        @case('PIMPINAN')
                            <li class="nav-item"><a class="nav-link" href="{{ route('pimpinan.dasbor') }}">Dasbor Pimpinan</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('pimpinan.surat.indeks') }}">Persetujuan Surat</a></li>
                            @break
                        @case('ORMAWA')
                            <li class="nav-item"><a class="nav-link" href="{{ route('ormawa.dasbor') }}">Dasbor Ormawa</a></li>
                            @break
                    @endswitch
                @endauth
            </ul>

            <div class="d-flex flex-wrap align-items-center gap-2">
                @auth
                    <a class="btn btn-outline-info btn-sm" href="{{ route('whatsapp.ikat') }}">Ikat WhatsApp</a>
                    @if (count(Auth::user()->daftarPeran()) > 1)
                        <a class="btn btn-outline-light btn-sm" href="{{ route('autentikasi.pilihperan') }}">Ganti Peran</a>
                    @endif
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button class="btn btn-outline-warning btn-sm" type="submit">Keluar</button>
                    </form>
                @else
                    <a class="btn btn-outline-light btn-sm" href="{{ route('login') }}">Masuk</a>
                @endauth
            </div>
        </div>
    </div>
</nav>
