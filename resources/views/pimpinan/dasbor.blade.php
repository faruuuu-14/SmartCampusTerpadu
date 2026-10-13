<!-- ========================================== -->
<!-- PEMBAHASAN / BAGIAN: Halaman Dasbor Pimpinan -->
<!-- ========================================== -->
@extends('layout.utama')

@section('judul', 'Dasbor Pimpinan')

@section('konten')
<div style="display: flex; flex-direction: column; gap: 1.5rem;">
    <div class="kartu" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h1 style="font-size: 1.5rem; font-weight: 800; margin-bottom: 0.25rem;">
                Portal Pimpinan: {{ $pengguna->NAMALENGKAP }}
            </h1>
            <p style="color: var(--warna-teks-redup); font-size: 0.9rem;">
                Jabatan Struktural: <strong style="color: #c084fc;">{{ $dosen?->JABATAN ?? 'Pimpinan' }}</strong> &bull; Level Persetujuan: <strong>Level {{ $dosen?->LEVELPERSETUJUAN ?? 1 }}</strong>
            </p>
        </div>
        <div style="text-align: right;">
            <span class="lencana-peran" style="background: rgba(168, 85, 247, 0.2); color: #c084fc; border-color: rgba(192, 132, 252, 0.3);">
                Peran Aktif: PIMPINAN
            </span>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem;">
        <div class="kartu" style="border-left: 4px solid #c084fc;">
            <div style="font-size: 0.8rem; color: var(--warna-teks-redup); text-transform: uppercase; font-weight: 700;">Menunggu TTE Digital</div>
            <div style="font-size: 1.75rem; font-weight: 800; margin: 0.5rem 0;">0 Berkas</div>
            <div style="font-size: 0.825rem; color: #d8b4fe;">Tanda Tangan Kunci Ed25519</div>
        </div>
        <div class="kartu" style="border-left: 4px solid var(--warna-sukses);">
            <div style="font-size: 0.8rem; color: var(--warna-teks-redup); text-transform: uppercase; font-weight: 700;">Surat Disetujui</div>
            <div style="font-size: 1.75rem; font-weight: 800; margin: 0.5rem 0;">{{ $pengguna->persetujuanSurat()->where('STATUSPERSETUJUAN', 'DISETUJUI')->count() }} Surat</div>
            <div style="font-size: 0.825rem; color: #34d399;">QR Code Telah Disematkan</div>
        </div>
        <div class="kartu" style="border-left: 4px solid var(--warna-aksen);">
            <div style="font-size: 0.8rem; color: var(--warna-teks-redup); text-transform: uppercase; font-weight: 700;">Kunci Kriptografi</div>
            <div style="font-size: 1.75rem; font-weight: 800; margin: 0.5rem 0;">Sodium Ed25519</div>
            <div style="font-size: 0.825rem; color: var(--warna-aksen);">Ekstensi PHP 8.5 Aktif</div>
        </div>
    </div>

    <div class="kartu">
        <h2 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 0.75rem;">Status Modul Sesi 1</h2>
        <p style="color: var(--warna-teks-redup); font-size: 0.875rem; line-height: 1.6;">
            Akun Anda terdeteksi memiliki peran ganda (DOSEN dan PIMPINAN) secara otomatis dari kolom <code>JABATAN</code> pada tabel <code>DOSEN</code>. Anda dapat berpindah portal kapan saja melalui tombol "⇄ Ganti Peran" di bilah navigasi atas.
        </p>
    </div>
</div>
@endsection
