<!-- ========================================== -->
<!-- PEMBAHASAN / BAGIAN: Antrian Verifikasi Pendaftaran Wajah -->
<!-- ========================================== -->
@extends('layout.app')

@section('judul', 'Verifikasi Biometrik')

@section('konten')
    <section class="kartu mb-4">
        <h1 class="h3 mb-1">Verifikasi pendaftaran biometrik</h1>
        <p class="teks-bantu mb-0">Hanya status AKTIF yang dapat dipakai untuk verifikasi presensi otomatis.</p>
    </section>
    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    <section class="kartu">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr><th>Mahasiswa</th><th>Persetujuan</th><th>Model</th><th>Aksi</th></tr></thead>
                <tbody>
                    @forelse ($mahasiswa as $item)
                        <tr>
                            <td>
                                <img class="rounded border mb-2" src="{{ route('baak.wajah.foto', $item) }}" alt="Foto referensi anonim" width="96" height="96" loading="lazy">
                                <span class="d-block fw-semibold">{{ $item->pengguna->NAMALENGKAP }}</span>
                                <span class="d-block small teks-bantu">{{ $item->NIM }}</span>
                            </td>
                            <td>{{ $item->TANGGALKONSENBIOMETRIK?->format('d/m/Y H:i') ?? 'Tidak tercatat' }}</td>
                            <td>{{ $item->MODELWAJAH }}</td>
                            <td class="d-flex gap-2">
                                <form method="POST" action="{{ route('baak.wajah.verifikasi', $item) }}">
                                    @csrf
                                    <input type="hidden" name="STATUSWAJAH" value="AKTIF">
                                    <button class="btn btn-sm btn-success" type="submit">Verifikasi aktif</button>
                                </form>
                                <form method="POST" action="{{ route('baak.wajah.verifikasi', $item) }}" onsubmit="return confirm('Tolak dan hapus foto serta embedding referensi ini?')">
                                    @csrf
                                    <input type="hidden" name="STATUSWAJAH" value="DITOLAK">
                                    <button class="btn btn-sm btn-outline-danger" type="submit">Tolak</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="daftar-tanpa-data">Tidak ada pendaftaran yang menunggu verifikasi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $mahasiswa->links() }}</div>
    </section>
@endsection
