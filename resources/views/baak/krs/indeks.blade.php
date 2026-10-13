<!-- ========================================== -->
<!-- PEMBAHASAN / BAGIAN: CRUD Pendaftaran KRS -->
<!-- ========================================== -->
@extends('layout.app')

@section('judul', 'Data KRS')

@section('konten')
    <section class="kartu mb-4">
        <h1 class="h3 mb-1">Kartu Rencana Studi</h1>
        <p class="teks-bantu mb-0">Atur pendaftaran mahasiswa ke jadwal mata kuliah.</p>
    </section>

    <section class="kartu mb-4">
        <h2 class="h5">Tambahkan mahasiswa ke kelas</h2>
        <form class="row g-3 align-items-end" method="POST" action="{{ route('baak.krs.store') }}">
            @csrf
            <div class="col-12 col-md-5">
                <label class="form-label fw-semibold" for="IDMAHASISWA">Mahasiswa</label>
                <select class="form-select" id="IDMAHASISWA" name="IDMAHASISWA" required>
                    <option value="">Pilih mahasiswa</option>
                    @foreach ($mahasiswa as $mhs)
                        <option value="{{ $mhs->IDMAHASISWA }}">{{ $mhs->NIM }} · {{ $mhs->pengguna?->NAMALENGKAP ?? $mhs->NAMAMAHASISWA }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-5">
                <label class="form-label fw-semibold" for="IDJADWAL">Mata kuliah</label>
                <select class="form-select" id="IDJADWAL" name="IDJADWAL" required>
                    <option value="">Pilih jadwal</option>
                    @foreach ($jadwal as $kelas)
                        <option value="{{ $kelas->IDJADWAL }}">{{ $kelas->KODEMATAKULIAH }} · {{ $kelas->NAMAMATAKULIAH }} ({{ $kelas->KELAS }})</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-2"><button class="btn btn-primary w-100" type="submit">Tambah KRS</button></div>
        </form>
    </section>

    <section class="kartu">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead><tr><th>Mahasiswa</th><th>Kelas</th><th>Pengampu</th><th>Ubah enrolmen</th><th>Aksi</th></tr></thead>
                <tbody>
                    @forelse ($krs as $item)
                        <tr>
                            <td>{{ $item->mahasiswa?->NIM }} · {{ $item->mahasiswa?->pengguna?->NAMALENGKAP }}</td>
                            <td>{{ $item->jadwal?->KODEMATAKULIAH }} · {{ $item->jadwal?->NAMAMATAKULIAH }} ({{ $item->jadwal?->KELAS }})</td>
                            <td>{{ $item->jadwal?->dosen?->NAMADOSEN }}</td>
                            <td>
                                <form class="d-flex flex-wrap gap-2" method="POST" action="{{ route('baak.krs.update', $item) }}">
                                    @csrf
                                    @method('PUT')
                                    <select class="form-select form-select-sm" name="IDMAHASISWA" aria-label="Mahasiswa" required>
                                        @foreach ($mahasiswa as $mhs)
                                            <option value="{{ $mhs->IDMAHASISWA }}" @selected($mhs->IDMAHASISWA === $item->IDMAHASISWA)>{{ $mhs->NIM }}</option>
                                        @endforeach
                                    </select>
                                    <select class="form-select form-select-sm" name="IDJADWAL" aria-label="Jadwal" required>
                                        @foreach ($jadwal as $kelas)
                                            <option value="{{ $kelas->IDJADWAL }}" @selected($kelas->IDJADWAL === $item->IDJADWAL)>{{ $kelas->KODEMATAKULIAH }} · {{ $kelas->KELAS }}</option>
                                        @endforeach
                                    </select>
                                    <button class="btn btn-sm btn-outline-primary" type="submit">Ubah</button>
                                </form>
                            </td>
                            <td>
                                <form method="POST" action="{{ route('baak.krs.destroy', $item) }}" onsubmit="return confirm('Hapus pendaftaran KRS ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" type="submit">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td class="daftar-tanpa-data" colspan="5">Belum ada pendaftaran KRS.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $krs->links() }}
    </section>
@endsection
