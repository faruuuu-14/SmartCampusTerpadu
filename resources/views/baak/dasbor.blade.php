<!-- ========================================== -->
<!-- PEMBAHASAN / BAGIAN: Halaman Dasbor BAAK -->
<!-- ========================================== -->
@extends('layout.utama')

@section('judul', 'Dasbor BAAK')

@section('konten')
<div style="display: flex; flex-direction: column; gap: 1.5rem;">
    <div class="kartu" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h1 style="font-size: 1.5rem; font-weight: 800; margin-bottom: 0.25rem;">
                Portal BAAK (Biro Administrasi Akademik & Kemahasiswaan)
            </h1>
            <p style="color: var(--warna-teks-redup); font-size: 0.9rem;">
                Petugas: <strong>{{ $pengguna->NAMALENGKAP }}</strong> &bull; Akun Staf BAAK
            </p>
        </div>
        <div style="text-align: right;">
            <span class="lencana-peran" style="background: rgba(236, 72, 153, 0.2); color: #f472b6; border-color: rgba(244, 114, 182, 0.3);">
                Peran Aktif: BAAK
            </span>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem;">
        <div class="kartu" style="border-left: 4px solid #ec4899;">
            <div style="font-size: 0.8rem; color: var(--warna-teks-redup); text-transform: uppercase; font-weight: 700;">Verifikasi Berkas Masuk</div>
            <div style="font-size: 1.75rem; font-weight: 800; margin: 0.5rem 0;">0 Surat</div>
            <div style="font-size: 0.825rem; color: #f472b6;">Antrean Pengajuan Surat</div>
        </div>
        <div class="kartu" style="border-left: 4px solid var(--warna-primer);">
            <div style="font-size: 0.8rem; color: var(--warna-teks-redup); text-transform: uppercase; font-weight: 700;">Total Mahasiswa Terdaftar</div>
            <div style="font-size: 1.75rem; font-weight: 800; margin: 0.5rem 0;">{{ \App\Models\Mahasiswa::count() }} Orang</div>
            <div style="font-size: 0.825rem; color: #a5b4fc;">Data Induk Mahasiswa</div>
        </div>
        <div class="kartu" style="border-left: 4px solid var(--warna-aksen);">
            <div style="font-size: 0.8rem; color: var(--warna-teks-redup); text-transform: uppercase; font-weight: 700;">Total Dosen & Tenaga Pengajar</div>
            <div style="font-size: 1.75rem; font-weight: 800; margin: 0.5rem 0;">{{ \App\Models\Dosen::count() }} Dosen</div>
            <div style="font-size: 0.825rem; color: var(--warna-aksen);">Data Induk Pengajar</div>
        </div>
    </div>

    <div class="kartu">
        <h2 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 0.75rem;">Status Modul Sesi 1</h2>
        <p style="color: var(--warna-teks-redup); font-size: 0.875rem; line-height: 1.6;">
            Akun BAAK dideteksi otomatis berdasarkan atribut <code>ADALAHBAAK = 1</code> pada tabel <code>PENGGUNA</code>. Panel verifikasi penerbitan nomor surat dan pengesahan TTE akan dihubungkan di sesi administrasi surat.
        </p>
    </div>
</div>
@endsection
