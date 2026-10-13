<!-- ========================================== -->
<!-- PEMBAHASAN / BAGIAN: Tugas dan Penilaian Dosen -->
<!-- ========================================== -->
@extends('layout.app')

@section('judul', 'Kelola Tugas')

@section('konten')
    <!-- ========================================== -->
    <!-- PEMBAHASAN / BAGIAN: Form Pembuatan Tugas -->
    <!-- ========================================== -->
    <section class="kartu mb-4">
        <h1 class="h3">Kelola Tugas</h1>
        @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif
        <form method="POST" action="{{ route('dosen.tugas.simpan') }}" class="row g-3">
            @csrf
            <div class="col-md-6">
                <label class="form-label" for="jadwal">Jadwal</label>
                <select class="form-select" id="jadwal" name="IDJADWAL" required>
                    <option value="">Pilih jadwal</option>
                    @foreach ($jadwal as $mataKuliah)
                        <option value="{{ $mataKuliah->IDJADWAL }}">{{ $mataKuliah->NAMAMATAKULIAH }} ({{ $mataKuliah->KELAS }})</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="judul">Judul tugas</label>
                <input class="form-control" id="judul" name="JUDULTUGAS" maxlength="255" required>
            </div>
            <div class="col-12">
                <label class="form-label" for="deskripsi">Deskripsi</label>
                <textarea class="form-control" id="deskripsi" name="DESKRIPSI" rows="2"></textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="tenggat">Tenggat</label>
                <input class="form-control" id="tenggat" type="datetime-local" name="TANGGALTENGGAT" required>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="mode">Mode pengerjaan</label>
                <select class="form-select" id="mode" name="MODEPENGERJAAN" required>
                    <option value="WAJIBKELAS">Wajib kelas</option>
                    <option value="BEBAS">Bebas</option>
                </select>
            </div>
            <div class="col-12"><button class="btn btn-primary" type="submit">Buat tugas</button></div>
        </form>
    </section>

    <!-- ========================================== -->
    <!-- PEMBAHASAN / BAGIAN: Daftar Pengumpulan dan Penilaian -->
    <!-- ========================================== -->
    @foreach ($jadwal as $mataKuliah)
        <section class="kartu mb-4">
            <h2 class="h5">{{ $mataKuliah->NAMAMATAKULIAH }} ({{ $mataKuliah->KELAS }})</h2>
            @forelse ($mataKuliah->tugas as $tugas)
                <h3 class="h6 border-top pt-3">{{ $tugas->JUDULTUGAS }} &middot; tenggat {{ $tugas->TANGGALTENGGAT?->format('d/m/Y H:i') }}</h3>
                <p>{{ $tugas->DESKRIPSI }}</p>
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead><tr><th>Mahasiswa</th><th>File</th><th>Status</th><th>Penilaian</th></tr></thead>
                        <tbody>
                            @forelse ($tugas->pengumpulan as $pengumpulan)
                                <tr>
                                    <td>{{ $pengumpulan->mahasiswa?->pengguna?->NAMALENGKAP }} ({{ $pengumpulan->mahasiswa?->NIM }})</td>
                                    <td>
                                        @if ($pengumpulan->FILEKUMPUL)
                                            <a href="{{ route('dosen.tugas.unduh', $pengumpulan) }}">Unduh file</a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ $pengumpulan->STATUSPENGUMPULAN }}</td>
                                    <td>
                                        <form class="d-flex flex-wrap gap-2" method="POST" action="{{ route('dosen.tugas.nilai', $pengumpulan) }}">
                                            @csrf
                                            <input class="form-control form-control-sm" style="max-width: 100px" type="number" min="0" max="100" step="0.01" name="NILAI" value="{{ $pengumpulan->NILAI }}" required aria-label="Nilai">
                                            <input class="form-control form-control-sm" style="max-width: 240px" type="text" name="UMPANBALIK" value="{{ $pengumpulan->UMPANBALIK }}" maxlength="2000" aria-label="Umpan balik">
                                            <button class="btn btn-sm btn-primary" type="submit">Simpan</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-secondary">Belum ada pengumpulan.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @empty
                <p class="text-secondary">Belum ada tugas untuk jadwal ini.</p>
            @endforelse
        </section>
    @endforeach
@endsection
