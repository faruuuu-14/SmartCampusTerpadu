<!-- ========================================== -->
<!-- PEMBAHASAN / BAGIAN: Antrean TTE Pimpinan -->
<!-- ========================================== -->
@extends('layout.app')

@section('judul', 'Persetujuan Surat')

@section('konten')
    <!-- ========================================== -->
    <!-- PEMBAHASAN / BAGIAN: Daftar Surat pada Giliran Pimpinan -->
    <!-- ========================================== -->
    <section class="kartu mb-4">
        <h1 class="h3 mb-1">Persetujuan Surat Elektronik</h1>
        <p class="text-secondary mb-0">Autentikasi ulang dengan kata sandi diperlukan untuk membuat tanda tangan Ed25519.</p>
    </section>
    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    @forelse ($persetujuan as $baris)
        @php($surat = $baris->administrasi)
        <section class="kartu mb-4">
            <h2 class="h5">{{ $surat->PERIHAL }} <span class="badge text-bg-secondary">{{ $surat->NOMORSURAT }}</span></h2>
            <p class="text-secondary">{{ $surat->JENISSURAT }} &middot; Urutan {{ $baris->URUTAN }} &middot; Pemohon {{ $surat->pemohon?->NAMALENGKAP }}</p>
            <dl class="row small">
                @foreach (json_decode($surat->ISISURATKANONIK, true) ?? [] as $kunci => $nilai)
                    <dt class="col-sm-3">{{ $kunci }}</dt>
                    <dd class="col-sm-9">{{ is_array($nilai) ? json_encode($nilai, JSON_UNESCAPED_UNICODE) : $nilai }}</dd>
                @endforeach
            </dl>
            <p class="small text-break"><strong>Hash isi:</strong> {{ $surat->HASHISI }}</p>
            <div class="row g-3 border-top pt-3">
                <div class="col-lg-6">
                    <form method="POST" action="{{ route('pimpinan.surat.setujui', $baris) }}">
                        @csrf
                        <label class="form-label" for="kata-sandi-{{ $baris->IDPERSETUJUAN }}">Ketik ulang kata sandi untuk menyetujui</label>
                        <input class="form-control mb-2" id="kata-sandi-{{ $baris->IDPERSETUJUAN }}" type="password" name="PASSWORD" autocomplete="current-password" required>
                        <button class="btn btn-success" type="submit">ACC dan tanda tangani</button>
                    </form>
                </div>
                <div class="col-lg-6">
                    <form method="POST" action="{{ route('pimpinan.surat.tolak', $baris) }}">
                        @csrf
                        <label class="form-label" for="catatan-tolak-{{ $baris->IDPERSETUJUAN }}">Catatan penolakan</label>
                        <textarea class="form-control mb-2" id="catatan-tolak-{{ $baris->IDPERSETUJUAN }}" name="CATATAN" rows="2" required maxlength="2000"></textarea>
                        <button class="btn btn-outline-danger" type="submit">Tolak surat</button>
                    </form>
                </div>
            </div>
        </section>
    @empty
        <section class="kartu text-secondary">Belum ada surat yang menunggu giliran persetujuan Anda.</section>
    @endforelse
@endsection
