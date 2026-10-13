<!-- ========================================== -->
<!-- PEMBAHASAN / BAGIAN: Daftar Akun dan Profil Kampus -->
<!-- ========================================== -->
@extends('layout.app')

@section('judul', 'Data Pengguna')

@section('konten')
    <section class="kartu mb-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h1 class="h3 mb-1">Data Pengguna</h1>
                <p class="teks-bantu mb-0">Kelola akun dan profil mahasiswa, dosen, pimpinan, serta petugas BAAK.</p>
            </div>
            <a class="btn btn-primary" href="{{ route('baak.pengguna.create') }}">Tambah akun</a>
        </div>
    </section>

    <section class="kartu">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr><th>Pengguna</th><th>Peran</th><th>Identitas</th><th>Status</th><th class="text-end">Aksi</th></tr>
                </thead>
                <tbody>
                    @forelse ($pengguna as $akun)
                        @php
                            $peran = $akun->ADALAHBAAK ? 'BAAK' : ($akun->mahasiswa ? 'MAHASISWA' : ($akun->dosen?->JABATAN ? 'PIMPINAN' : 'DOSEN'));
                            $identitas = $akun->mahasiswa?->NIM ?? $akun->dosen?->NIDN ?? '—';
                        @endphp
                        <tr>
                            <td><div class="fw-bold">{{ $akun->NAMALENGKAP }}</div><div class="small teks-bantu">{{ $akun->USERNAME }} · {{ $akun->EMAIL ?? 'Tanpa email' }}</div></td>
                            <td>{{ $peran }}</td>
                            <td>{{ $identitas }}</td>
                            <td><span class="badge {{ $akun->STATUSAKUN === 'AKTIF' ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $akun->STATUSAKUN }}</span></td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-2">
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('baak.pengguna.edit', $akun) }}">Ubah</a>
                                    @if ($akun->STATUSAKUN === 'AKTIF' && ! $akun->is(auth()->user()))
                                        <form action="{{ route('baak.pengguna.destroy', $akun) }}" method="POST" onsubmit="return confirm('Nonaktifkan akun ini? Riwayatnya tetap disimpan.')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger" type="submit">Nonaktifkan</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td class="daftar-tanpa-data" colspan="5">Belum ada data pengguna.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $pengguna->links() }}</div>
    </section>
@endsection
