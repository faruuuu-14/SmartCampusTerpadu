<!-- ========================================== -->
<!-- PEMBAHASAN / BAGIAN: Tautan Menu yang Dibatasi oleh Peran Aktif -->
<!-- ========================================== -->
@php($peranAktif = session('PERANAKTIF'))
<div class="menu-kelompok">
    <div class="menu-judul">Utama</div>
    @if ($peranAktif === 'MAHASISWA')
        <a class="menu-tautan {{ request()->routeIs('mahasiswa.dasbor') ? 'aktif' : '' }}" href="{{ route('mahasiswa.dasbor') }}"><span class="menu-ikon">⌂</span>Dasbor</a>
        <a class="menu-tautan {{ request()->routeIs('mahasiswa.tugas.*') ? 'aktif' : '' }}" href="{{ route('mahasiswa.tugas.indeks') }}"><span class="menu-ikon">▣</span>Kelas &amp; Tugas</a>
        <a class="menu-tautan {{ request()->routeIs('mahasiswa.surat.*') ? 'aktif' : '' }}" href="{{ route('mahasiswa.surat.indeks') }}"><span class="menu-ikon">▤</span>Surat &amp; Administrasi</a>
        <a class="menu-tautan {{ request()->routeIs('mahasiswa.wajah.*') ? 'aktif' : '' }}" href="{{ route('mahasiswa.wajah.indeks') }}"><span class="menu-ikon">◉</span>Persetujuan Biometrik</a>
        <a class="menu-tautan" href="{{ route('whatsapp.ikat') }}"><span class="menu-ikon">↗</span>Sambungkan WhatsApp</a>
    @elseif ($peranAktif === 'DOSEN')
        <a class="menu-tautan {{ request()->routeIs('dosen.dasbor') ? 'aktif' : '' }}" href="{{ route('dosen.dasbor') }}"><span class="menu-ikon">⌂</span>Dasbor</a>
        <a class="menu-tautan {{ request()->routeIs('dosen.pertemuan.*') ? 'aktif' : '' }}" href="{{ route('dosen.pertemuan.indeks') }}"><span class="menu-ikon">◷</span>Pertemuan</a>
        <a class="menu-tautan {{ request()->routeIs('dosen.rekap.*') ? 'aktif' : '' }}" href="{{ route('dosen.rekap.indeks') }}"><span class="menu-ikon">▥</span>Rekap Presensi</a>
        <a class="menu-tautan {{ request()->routeIs('dosen.tugas.*') ? 'aktif' : '' }}" href="{{ route('dosen.tugas.indeks') }}"><span class="menu-ikon">▣</span>Kelola Tugas</a>
        <a class="menu-tautan" href="{{ route('whatsapp.ikat') }}"><span class="menu-ikon">↗</span>Sambungkan WhatsApp</a>
    @elseif ($peranAktif === 'BAAK')
        <a class="menu-tautan {{ request()->routeIs('baak.dasbor') ? 'aktif' : '' }}" href="{{ route('baak.dasbor') }}"><span class="menu-ikon">⌂</span>Dasbor</a>
        <a class="menu-tautan {{ request()->routeIs('baak.pengguna.*') ? 'aktif' : '' }}" href="{{ route('baak.pengguna.index') }}"><span class="menu-ikon">♙</span>Data Pengguna</a>
        <a class="menu-tautan {{ request()->routeIs('baak.jadwal.*') ? 'aktif' : '' }}" href="{{ route('baak.jadwal.index') }}"><span class="menu-ikon">◷</span>Jadwal Kuliah</a>
        <a class="menu-tautan {{ request()->routeIs('baak.krs.*') ? 'aktif' : '' }}" href="{{ route('baak.krs.index') }}"><span class="menu-ikon">▦</span>Kelola KRS</a>
        <a class="menu-tautan {{ request()->routeIs('baak.surat.*') ? 'aktif' : '' }}" href="{{ route('baak.surat.indeks') }}"><span class="menu-ikon">▤</span>Verifikasi Surat</a>
        <a class="menu-tautan {{ request()->routeIs('baak.wajah.*') ? 'aktif' : '' }}" href="{{ route('baak.wajah.indeks') }}"><span class="menu-ikon">◉</span>Verifikasi Biometrik</a>
    @elseif ($peranAktif === 'PIMPINAN')
        <a class="menu-tautan {{ request()->routeIs('pimpinan.dasbor') ? 'aktif' : '' }}" href="{{ route('pimpinan.dasbor') }}"><span class="menu-ikon">⌂</span>Dasbor</a>
        <a class="menu-tautan {{ request()->routeIs('pimpinan.surat.*') ? 'aktif' : '' }}" href="{{ route('pimpinan.surat.indeks') }}"><span class="menu-ikon">▤</span>Persetujuan Surat</a>
    @elseif ($peranAktif === 'ORMAWA')
        <a class="menu-tautan {{ request()->routeIs('ormawa.dasbor') ? 'aktif' : '' }}" href="{{ route('ormawa.dasbor') }}"><span class="menu-ikon">⌂</span>Dasbor Organisasi</a>
        <a class="menu-tautan {{ request()->routeIs('autentikasi.pilihperan') ? 'aktif' : '' }}" href="{{ route('autentikasi.pilihperan') }}"><span class="menu-ikon">▤</span>Layanan Mahasiswa</a>
    @endif
</div>

<!-- ========================================== -->
<!-- PEMBAHASAN / BAGIAN: Status Konektivitas Kanal Tambahan -->
<!-- ========================================== -->
<div class="mt-4 rounded-4 p-3" style="background:#f2f8fb">
    <div class="small fw-bold mb-1">Layanan lintas kanal</div>
    <div class="small teks-bantu">WhatsApp terhubung ke portal. Data akademik tetap dikelola dari web.</div>
</div>
