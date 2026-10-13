<!-- ========================================== -->
<!-- PEMBAHASAN / BAGIAN: Halaman Dasbor Mahasiswa -->
<!-- ========================================== -->
@extends('layout.utama')

@section('judul', 'Dasbor Mahasiswa')

@section('konten')
<div style="display: flex; flex-direction: column; gap: 1.5rem;">
    <div class="kartu" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h1 style="font-size: 1.5rem; font-weight: 800; margin-bottom: 0.25rem;">
                Selamat Datang, {{ $pengguna->NAMALENGKAP }}!
            </h1>
            <p style="color: var(--warna-teks-redup); font-size: 0.9rem;">
                NIM: <strong>{{ $mahasiswa?->NIM ?? '-' }}</strong> &bull; Program Studi: <strong>{{ $mahasiswa?->PROGRAMSTUDI ?? '-' }}</strong> &bull; Angkatan: <strong>{{ $mahasiswa?->ANGKATAN ?? '-' }}</strong>
            </p>
        </div>
        <div style="text-align: right;">
            <span class="lencana-peran" style="background: rgba(16, 185, 129, 0.2); color: #34d399; border-color: rgba(52, 211, 153, 0.3);">
                Peran Aktif: MAHASISWA
            </span>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem;">
        <div class="kartu" style="border-left: 4px solid var(--warna-aksen);">
            <div style="font-size: 0.8rem; color: var(--warna-teks-redup); text-transform: uppercase; font-weight: 700;">Presensi Hari Ini</div>
            <div style="font-size: 1.75rem; font-weight: 800; margin: 0.5rem 0;">Siap Hadir</div>
            <div style="font-size: 0.825rem; color: var(--warna-aksen);">Verifikasi Biometrik & Radius GPS</div>
        </div>
        <div class="kartu" style="border-left: 4px solid var(--warna-primer);">
            <div style="font-size: 0.8rem; color: var(--warna-teks-redup); text-transform: uppercase; font-weight: 700;">Rencana Studi (KRS)</div>
            <div style="font-size: 1.75rem; font-weight: 800; margin: 0.5rem 0;">{{ $mahasiswa?->krs()->count() ?? 0 }} Mata Kuliah</div>
            <div style="font-size: 0.825rem; color: #a5b4fc;">Semester Aktif Terdaftar</div>
        </div>
        <div class="kartu" style="border-left: 4px solid #f59e0b;">
            <div style="font-size: 0.8rem; color: var(--warna-teks-redup); text-transform: uppercase; font-weight: 700;">Pengajuan Surat</div>
            <div style="font-size: 1.75rem; font-weight: 800; margin: 0.5rem 0;">{{ $pengguna->administrasi()->count() }} Berkas</div>
            <div style="font-size: 0.825rem; color: #fbbf24;">Administrasi Terintegrasi TTE</div>
        </div>
    </div>

    <div class="kartu">
        <h2 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 0.75rem;">Status Modul Sesi 1</h2>
        <p style="color: var(--warna-teks-redup); font-size: 0.875rem; line-height: 1.6;">
            Autentikasi Satu Pintu Masuk, deteksi peran multi-role, dan struktur database 12 tabel telah aktif. Fitur presensi anti-titip absen serta pengajuan surat dengan verifikasi TTE Ed25519 siap dibangun pada sesi berikutnya.
        </p>
    </div>
</div>
@endsection
