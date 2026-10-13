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
                <div class="border-top mt-3 pt-3">
                    <div class="d-flex flex-wrap justify-content-between gap-2">
                        <div>
                            <h3 class="h6 mb-1">{{ $tugas->JUDULTUGAS }} &middot; tenggat {{ $tugas->TANGGALTENGGAT?->format('d/m/Y H:i') }}</h3>
                            <p>{{ $tugas->DESKRIPSI }}</p>
                        </div>
                        <form method="POST" action="{{ route('dosen.tugas.hapus', $tugas) }}" onsubmit="return confirm('Hapus tugas ini?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" type="submit" @disabled($tugas->pengumpulan->isNotEmpty())>Hapus tugas</button>
                        </form>
                    </div>
                    <form method="POST" action="{{ route('dosen.tugas.ubah', $tugas) }}" class="row g-2 mb-3">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="IDJADWAL" value="{{ $mataKuliah->IDJADWAL }}">
                        <div class="col-md-4"><label class="visually-hidden" for="judul-{{ $tugas->IDTUGAS }}">Judul tugas</label><input class="form-control form-control-sm" id="judul-{{ $tugas->IDTUGAS }}" name="JUDULTUGAS" value="{{ $tugas->JUDULTUGAS }}" maxlength="255" required></div>
                        <div class="col-md-3"><label class="visually-hidden" for="tenggat-{{ $tugas->IDTUGAS }}">Tenggat</label><input class="form-control form-control-sm" id="tenggat-{{ $tugas->IDTUGAS }}" type="datetime-local" name="TANGGALTENGGAT" value="{{ $tugas->TANGGALTENGGAT?->format('Y-m-d\TH:i') }}" required></div>
                        <div class="col-md-3"><label class="visually-hidden" for="mode-{{ $tugas->IDTUGAS }}">Mode pengerjaan</label><select class="form-select form-select-sm" id="mode-{{ $tugas->IDTUGAS }}" name="MODEPENGERJAAN"><option value="WAJIBKELAS" @selected($tugas->MODEPENGERJAAN === 'WAJIBKELAS')>Wajib kelas</option><option value="BEBAS" @selected($tugas->MODEPENGERJAAN === 'BEBAS')>Bebas</option></select></div>
                        <div class="col-md-2"><button class="btn btn-sm btn-outline-primary w-100" type="submit">Simpan ubahan</button></div>
                        <div class="col-12"><label class="visually-hidden" for="deskripsi-{{ $tugas->IDTUGAS }}">Deskripsi</label><textarea class="form-control form-control-sm" id="deskripsi-{{ $tugas->IDTUGAS }}" name="DESKRIPSI" rows="2">{{ $tugas->DESKRIPSI }}</textarea></div>
                    </form>
                </div>
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
