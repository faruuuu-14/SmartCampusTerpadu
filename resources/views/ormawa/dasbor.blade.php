<!-- ========================================== -->
<!-- PEMBAHASAN / BAGIAN: Halaman Dasbor Ormawa -->
<!-- ========================================== -->
@extends('layout.app')

@section('judul', 'Dasbor Pengurus Ormawa')

@section('konten')
<div class="d-flex flex-column gap-4">
    <section class="kartu">
        <span class="lencana-peran">Peran Aktif: ORMAWA</span>
        <h1 class="h3 mt-3 mb-2">Portal Organisasi Mahasiswa</h1>
        <p class="teks-bantu mb-0">
            Pengurus: <strong>{{ $pengguna->NAMALENGKAP }}</strong>
            &middot; Organisasi: <strong>{{ $organisasi?->NAMAORGANISASI ?? 'Organisasi Mahasiswa' }}</strong>
            &middot; Periode: {{ $organisasi?->PERIODE ?? '2025/2026' }}
        </p>
    </section>

    <section class="row g-3">
        <div class="col-12 col-md-6">
            <article class="kartu h-100">
                <h2 class="h6 teks-bantu">Dosen Pembina</h2>
                <p class="h5 mb-1">{{ $organisasi?->pembina?->NAMADOSEN ?? 'Belum ditetapkan' }}</p>
                <p class="small teks-bantu mb-0">Pembimbing resmi kegiatan organisasi</p>
            </article>
        </div>
        <div class="col-12 col-md-6">
            <article class="kartu h-100">
                <h2 class="h6 teks-bantu">Administrasi Organisasi</h2>
                <p class="mb-0">Gunakan menu <strong>Proposal &amp; Administrasi</strong> untuk mengajukan surat organisasi.</p>
            </article>
        </div>
    </section>

    <section class="kartu">
        <h2 class="h5 mb-2">Layanan Ormawa</h2>
        <p class="teks-bantu mb-0">
            Peran ORMAWA dideteksi secara dinamis karena mahasiswa tercatat sebagai <code>IDKETUA</code> pada tabel <code>ORGANISASI</code> yang berstatus aktif. Anda dapat berganti antara portal Mahasiswa dan portal Ormawa dengan mulus.
        </p>
    </section>
</div>
@endsection
