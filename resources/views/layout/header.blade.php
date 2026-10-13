<!-- ========================================== -->
<!-- PEMBAHASAN / BAGIAN: Bilah Atas Portal dan Profil Pengguna -->
<!-- ========================================== -->
<header class="portal-header">
    <div class="d-flex align-items-center gap-2">
        @auth
            <!-- ========================================== -->
            <!-- PEMBAHASAN / BAGIAN: Tombol Navigasi Seluler -->
            <!-- ========================================== -->
            <button class="btn btn-light border d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#menuSeluler" aria-controls="menuSeluler" aria-label="Buka navigasi">☰</button>
        @endauth
        <a class="merek-portal" href="{{ Auth::check() ? route(strtolower(session('PERANAKTIF', 'mahasiswa')).'.dasbor') : route('login') }}">
            <span class="logo-portal" aria-hidden="true">SC</span>
            <span>
                <span class="d-block">Smart Campus</span>
                <small class="d-block teks-bantu fw-normal">Portal Terpadu</small>
            </span>
        </a>
    </div>

    <!-- ========================================== -->
    <!-- PEMBAHASAN / BAGIAN: Menu Identitas dan Aksi Akun -->
    <!-- ========================================== -->
    <div class="d-flex align-items-center gap-2 gap-md-3">
        @auth
            <div class="d-none d-sm-block text-end">
                <div class="fw-bold">{{ Auth::user()->NAMALENGKAP }}</div>
                <small class="teks-bantu">{{ session('PERANAKTIF') }}</small>
            </div>
            @if (count(Auth::user()->daftarPeran()) > 1)
                <a class="btn btn-light border btn-sm" href="{{ route('autentikasi.pilihperan') }}">Ganti peran</a>
            @endif
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button class="btn btn-outline-secondary btn-sm" type="submit">Keluar</button>
            </form>
        @else
            <span class="small teks-bantu d-none d-sm-inline">Portal Akademik &amp; Administrasi</span>
        @endauth
    </div>
</header>
