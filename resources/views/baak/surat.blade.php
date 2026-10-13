<!-- ========================================== -->
<!-- PEMBAHASAN / BAGIAN: Pemeriksaan Surat Masuk oleh BAAK -->
<!-- ========================================== -->
@extends('layout.app')

@section('judul', 'Verifikasi Surat BAAK')

@section('konten')
    <!-- ========================================== -->
    <!-- PEMBAHASAN / BAGIAN: Pemberitahuan dan Daftar Surat -->
    <!-- ========================================== -->
    <section class="kartu mb-4">
        <h1 class="h3 mb-1">Verifikasi Surat Masuk</h1>
        <p class="text-secondary mb-0">Periksa identitas, keperluan, dan lampiran sebelum meloloskan surat ke pimpinan.</p>
    </section>
    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    @forelse ($suratMasuk as $surat)
        <section class="kartu mb-4">
            <div class="d-flex flex-wrap justify-content-between gap-3">
                <div>
                    <h2 class="h5 mb-1">{{ $surat->PERIHAL }}</h2>
                    <p class="mb-1">{{ $surat->JENISSURAT }} &middot; {{ $surat->STATUSADMINISTRASI }}</p>
                    <p class="small text-secondary mb-1">
                        Pemohon: {{ $surat->pemohon?->NAMALENGKAP ?? '-' }}
                        ({{ $surat->pemohon?->mahasiswa?->NIM ?? '-' }})
                    </p>
                    <p class="mb-2">{{ $surat->KEPERLUAN }}</p>
                    @if ($surat->FILELAMPIRAN)
                        <a class="btn btn-sm btn-outline-primary" href="{{ route('baak.surat.lampiran', $surat) }}">Unduh lampiran privat</a>
                    @else
                        <span class="small text-secondary">Pemohon tidak melampirkan berkas.</span>
                    @endif
                </div>
                <div class="text-secondary small">Diajukan {{ $surat->TANGGALBUAT?->format('d/m/Y H:i') }}</div>
            </div>

            <form class="border-top mt-3 pt-3" method="POST" action="{{ route('baak.surat.tinjau', $surat) }}">
                @csrf
                <fieldset class="mb-3">
                    <legend class="h6">Ceklist verifikasi</legend>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="CEKLIST[]" value="IDENTITAS" id="identitas-{{ $surat->IDADMINISTRASI }}">
                        <label class="form-check-label" for="identitas-{{ $surat->IDADMINISTRASI }}">Identitas pemohon sesuai</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="CEKLIST[]" value="KEPERLUAN" id="keperluan-{{ $surat->IDADMINISTRASI }}">
                        <label class="form-check-label" for="keperluan-{{ $surat->IDADMINISTRASI }}">Jenis dan keperluan surat sesuai</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="CEKLIST[]" value="LAMPIRAN" id="lampiran-{{ $surat->IDADMINISTRASI }}">
                        <label class="form-check-label" for="lampiran-{{ $surat->IDADMINISTRASI }}">Lampiran diperiksa / tidak diperlukan</label>
                    </div>
                </fieldset>
                <label class="form-label" for="catatan-{{ $surat->IDADMINISTRASI }}">Catatan revisi (wajib bila meminta revisi)</label>
                <textarea class="form-control mb-3" id="catatan-{{ $surat->IDADMINISTRASI }}" name="CATATAN" rows="2" maxlength="2000"></textarea>
                <div class="d-flex flex-wrap gap-2">
                    <button class="btn btn-primary" name="KEPUTUSAN" value="LANJUT" type="submit">Setujui lanjut</button>
                    <button class="btn btn-outline-danger" name="KEPUTUSAN" value="REVISI" type="submit">Minta revisi</button>
                </div>
            </form>
        </section>
    @empty
        <section class="kartu text-secondary">Tidak ada surat yang menunggu verifikasi.</section>
    @endforelse
@endsection
