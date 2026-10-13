<!-- ========================================== -->
<!-- PEMBAHASAN / BAGIAN: Halaman Dasbor Dosen -->
<!-- ========================================== -->
@extends('layout.utama')

@section('judul', 'Dasbor Dosen')

@section('konten')
<div style="display: flex; flex-direction: column; gap: 1.5rem;">
    <div class="kartu" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h1 style="font-size: 1.5rem; font-weight: 800; margin-bottom: 0.25rem;">
                Selamat Datang, {{ $pengguna->NAMALENGKAP }}!
            </h1>
            <p style="color: var(--warna-teks-redup); font-size: 0.9rem;">
                NIDN: <strong>{{ $dosen?->NIDN ?? '-' }}</strong> &bull; Program Studi: <strong>{{ $dosen?->PROGRAMSTUDI ?? '-' }}</strong>
                @if($dosen?->JABATAN)
                    &bull; Jabatan Tambahan: <strong style="color: #c084fc;">{{ $dosen->JABATAN }}</strong>
                @endif
            </p>
        </div>
        <div style="text-align: right;">
            <span class="lencana-peran" style="background: rgba(59, 130, 246, 0.2); color: #60a5fa; border-color: rgba(96, 165, 250, 0.3);">
                Peran Aktif: DOSEN
            </span>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem;">
        <div class="kartu" style="border-left: 4px solid var(--warna-primer);">
            <div style="font-size: 0.8rem; color: var(--warna-teks-redup); text-transform: uppercase; font-weight: 700;">Jadwal Mengajar</div>
            <div style="font-size: 1.75rem; font-weight: 800; margin: 0.5rem 0;">{{ $dosen?->jadwal()->count() ?? 0 }} Kelas</div>
            <div style="font-size: 0.825rem; color: #a5b4fc;">Semester Aktif</div>
        </div>
        <div class="kartu" style="border-left: 4px solid var(--warna-aksen);">
            <div style="font-size: 0.8rem; color: var(--warna-teks-redup); text-transform: uppercase; font-weight: 700;">Bimbingan Akademik (PA)</div>
            <div style="font-size: 1.75rem; font-weight: 800; margin: 0.5rem 0;">{{ $dosen?->mahasiswaBimbingan()->count() ?? 0 }} Mahasiswa</div>
            <div style="font-size: 0.825rem; color: var(--warna-aksen);">Daftar Mahasiswa Bimbingan</div>
        </div>
        <div class="kartu" style="border-left: 4px solid #10b981;">
            <div style="font-size: 0.8rem; color: var(--warna-teks-redup); text-transform: uppercase; font-weight: 700;">Tugas & Penilaian</div>
            <div style="font-size: 1.75rem; font-weight: 800; margin: 0.5rem 0;">{{ $dosen?->tugas()->count() ?? 0 }} Tugas</div>
            <div style="font-size: 0.825rem; color: #34d399;">Integrasi Bot WhatsApp & Web</div>
        </div>
    </div>

    <div class="kartu">
        <h2 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 0.75rem;">Status Modul Sesi 1</h2>
        <p style="color: var(--warna-teks-redup); font-size: 0.875rem; line-height: 1.6;">
            Akun dosen terautentikasi dengan benar. Fitur absensi anti titip absen berbasis koordinat GPS dan embedding biometrik wajah akan dikembangkan pada tahap selanjutnya.
        </p>
    </div>
</div>
@endsection
