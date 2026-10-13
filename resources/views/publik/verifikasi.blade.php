<!-- ========================================== -->
<!-- PEMBAHASAN / BAGIAN: Halaman Publik Verifikasi TTE -->
<!-- ========================================== -->
@extends('layout.app')

@section('judul', 'Verifikasi TTE Publik')

@section('konten')
    <!-- ========================================== -->
    <!-- PEMBAHASAN / BAGIAN: Informasi TTE Internal Tidak Tersertifikasi -->
    <!-- ========================================== -->
    <section class="kartu mb-4">
        <h1 class="h3">Verifikasi Tanda Tangan Elektronik</h1>
        <div class="alert alert-warning mb-0">
            TTE ini tidak tersertifikasi dan digunakan untuk kebutuhan internal Smart Campus Terpadu.
            Hasil verifikasi bukan validasi dari penyelenggara sertifikasi elektronik.
        </div>
    </section>

    @if (! $tokenDitemukan)
        <!-- ========================================== -->
        <!-- PEMBAHASAN / BAGIAN: Pesan Token Verifikasi Tidak Ditemukan -->
        <!-- ========================================== -->
        <section class="kartu" role="alert">
            <h2 class="h5">Token verifikasi tidak ditemukan</h2>
            <p class="mb-0">Periksa kembali QR Code atau tautan verifikasi. Token ini tidak terdaftar atau sudah tidak berlaku.</p>
        </section>
    @else
        <!-- ========================================== -->
        <!-- PEMBAHASAN / BAGIAN: Ringkasan Surat dan Pemohon -->
        <!-- ========================================== -->
        <section class="kartu mb-4">
            <h2 class="h4">{{ $surat->PERIHAL }}</h2>
            <dl class="row mb-0">
                <dt class="col-sm-4">Jenis surat</dt>
                <dd class="col-sm-8">{{ $surat->JENISSURAT }}</dd>
                <dt class="col-sm-4">Nomor surat</dt>
                <dd class="col-sm-8">{{ $surat->NOMORSURAT }}</dd>
                <dt class="col-sm-4">Pemohon</dt>
                <dd class="col-sm-8">{{ $surat->pemohon?->NAMALENGKAP ?? 'Tidak diketahui' }}</dd>
                <dt class="col-sm-4">Status penerbitan</dt>
                <dd class="col-sm-8">{{ $surat->STATUSADMINISTRASI }}</dd>
            </dl>
        </section>

        <!-- ========================================== -->
        <!-- PEMBAHASAN / BAGIAN: Hasil Seluruh Pemeriksaan Penandatangan -->
        <!-- ========================================== -->
        <section class="kartu mb-4">
            <h2 class="h4">Hasil pemeriksaan penandatangan</h2>
            @foreach ($hasilPenandatangan as $baris)
                @php($persetujuan = $baris['persetujuan'])
                @php($hasil = $baris['hasil'])
                <article class="border rounded p-3 mb-3">
                    <div class="d-flex flex-wrap justify-content-between gap-2">
                        <div>
                            <h3 class="h6 mb-1">{{ $persetujuan->penyetuju?->NAMALENGKAP ?? 'Penyetuju tidak ditemukan' }}</h3>
                            <p class="small text-secondary mb-1">
                                {{ $persetujuan->penyetuju?->dosen?->JABATAN ?? 'Jabatan tidak diketahui' }}
                                &middot; {{ $persetujuan->WAKTUKEPUTUSAN?->format('d/m/Y H:i:s') ?? 'Waktu tidak tersedia' }}
                            </p>
                        </div>
                        <span class="badge {{ $hasil['valid'] ? 'text-bg-success' : 'text-bg-danger' }}">
                            {{ $hasil['valid'] ? 'VALID' : 'TIDAK VALID' }}
                        </span>
                    </div>
                    <ul class="small mb-2">
                        <li>Tanda tangan Ed25519: {{ $hasil['tanda_tangan'] ? 'VALID' : 'TIDAK VALID' }}</li>
                        <li>Sidik jari kunci: {{ $hasil['sidik_jari'] ? 'COCOK' : 'TIDAK COCOK' }}</li>
                        <li>SHA-256 isi dan HASHISI pada muatan: {{ $hasil['hash_isi'] ? 'COCOK' : 'TIDAK COCOK' }}</li>
                    </ul>
                    @unless ($hasil['valid'])
                        <p class="small text-danger mb-0"><strong>Alasan:</strong> {{ implode(' ', $hasil['alasan']) }}</p>
                    @endunless
                </article>
            @endforeach
            @php($semuaValid = $hasilPenandatangan->isNotEmpty() && $hasilPenandatangan->every(fn ($baris) => $baris['hasil']['valid']))
            <div class="alert {{ $semuaValid ? 'alert-success' : 'alert-danger' }} mb-0">
                Status akhir TTE: <strong>{{ $semuaValid ? 'VALID' : 'TIDAK VALID' }}</strong>
                @unless ($semuaValid)
                    <div>Satu atau lebih pemeriksaan tanda tangan, sidik jari kunci, hash isi, atau identitas muatan gagal.</div>
                @endunless
            </div>
        </section>

        <!-- ========================================== -->
        <!-- PEMBAHASAN / BAGIAN: Perbandingan Berkas PDF Final -->
        <!-- ========================================== -->
        <section class="kartu">
            <h2 class="h4">Periksa integritas PDF</h2>
            <p>
                Hash berkas tersimpan:
                <code class="text-break">{{ $surat->HASHBERKAS ?? 'Belum tersedia' }}</code>
                &middot;
                PDF sistem {{ $validBerkasSistem ? 'utuh' : 'tidak tersedia atau berubah' }}.
            </p>
            @if (session('perbandingan_pdf'))
                <div class="alert {{ session('perbandingan_pdf.hasil') === 'SAMA' ? 'alert-success' : 'alert-danger' }}">
                    PDF unggahan:
                    <strong>{{ session('perbandingan_pdf.hasil') }}</strong>
                    <div class="small text-break">SHA-256 berkas: {{ session('perbandingan_pdf.hash_unggahan') }}</div>
                </div>
            @endif
            @if ($errors->has('BERKASPDF'))
                <div class="alert alert-danger">{{ $errors->first('BERKASPDF') }}</div>
            @endif
            <form method="POST" action="{{ route('tte.verifikasi.bandingkan', $persetujuanUtama->TOKENVERIFIKASI) }}" enctype="multipart/form-data">
                @csrf
                <label class="form-label" for="berkas-pdf">Unggah PDF yang akan diperiksa (maksimal 20 MB)</label>
                <input class="form-control mb-3" id="berkas-pdf" type="file" name="BERKASPDF" accept="application/pdf,.pdf" required>
                <button class="btn btn-primary" type="submit">Bandingkan SHA-256</button>
            </form>
        </section>
    @endif
@endsection
