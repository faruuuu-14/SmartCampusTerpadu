<!-- ========================================== -->
<!-- PEMBAHASAN / BAGIAN: Halaman Dasbor Ormawa -->
<!-- ========================================== -->
@extends('layout.utama')

@section('judul', 'Dasbor Pengurus Ormawa')

@section('konten')
<div style="display: flex; flex-direction: column; gap: 1.5rem;">
    <div class="kartu" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h1 style="font-size: 1.5rem; font-weight: 800; margin-bottom: 0.25rem;">
                Portal Pengurus Ormawa: {{ $pengguna->NAMALENGKAP }}
            </h1>
            <p style="color: var(--warna-teks-redup); font-size: 0.9rem;">
                Organisasi: <strong style="color: #fbbf24;">{{ $organisasi?->NAMAORGANISASI ?? 'Organisasi Mahasiswa' }}</strong> &bull; Periode: <strong>{{ $organisasi?->PERIODE ?? '2025/2026' }}</strong>
            </p>
        </div>
        <div style="text-align: right;">
            <span class="lencana-peran" style="background: rgba(245, 158, 11, 0.2); color: #fbbf24; border-color: rgba(251, 191, 36, 0.3);">
                Peran Aktif: ORMAWA
            </span>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem;">
        <div class="kartu" style="border-left: 4px solid #f59e0b;">
            <div style="font-size: 0.8rem; color: var(--warna-teks-redup); text-transform: uppercase; font-weight: 700;">Proposal Kegiatan</div>
            <div style="font-size: 1.75rem; font-weight: 800; margin: 0.5rem 0;">0 Dokumen</div>
            <div style="font-size: 0.825rem; color: #fbbf24;">Pengajuan Proposal Ormawa</div>
        </div>
        <div class="kartu" style="border-left: 4px solid var(--warna-primer);">
            <div style="font-size: 0.8rem; color: var(--warna-teks-redup); text-transform: uppercase; font-weight: 700;">Laporan Pertanggungjawaban (LPJ)</div>
            <div style="font-size: 1.75rem; font-weight: 800; margin: 0.5rem 0;">0 Dokumen</div>
            <div style="font-size: 0.825rem; color: #a5b4fc;">Pelaporan Dana & Kegiatan</div>
        </div>
        <div class="kartu" style="border-left: 4px solid var(--warna-sukses);">
            <div style="font-size: 0.8rem; color: var(--warna-teks-redup); text-transform: uppercase; font-weight: 700;">Dosen Pembina</div>
            <div style="font-size: 1.1rem; font-weight: 700; margin: 0.75rem 0;">{{ $organisasi?->pembina?->NAMADOSEN ?? '-' }}</div>
            <div style="font-size: 0.825rem; color: #34d399;">Pembimbing Resmi Kegiatan</div>
        </div>
    </div>

    <div class="kartu">
        <h2 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 0.75rem;">Status Modul Sesi 1</h2>
        <p style="color: var(--warna-teks-redup); font-size: 0.875rem; line-height: 1.6;">
            Peran ORMAWA dideteksi secara dinamis karena mahasiswa tercatat sebagai <code>IDKETUA</code> pada tabel <code>ORGANISASI</code> yang berstatus aktif. Anda dapat berganti antara portal Mahasiswa dan portal Ormawa dengan mulus.
        </p>
    </div>
</div>
@endsection
