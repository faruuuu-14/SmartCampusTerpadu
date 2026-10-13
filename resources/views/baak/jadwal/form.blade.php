<!-- ========================================== -->
<!-- PEMBAHASAN / BAGIAN: Form Data Jadwal Kuliah -->
<!-- ========================================== -->
@extends('layout.app')

@section('judul', $jadwal ? 'Ubah Jadwal' : 'Tambah Jadwal')

@section('konten')
    <div class="mx-auto" style="max-width: 1000px">
        <section class="kartu mb-4">
            <a class="small text-decoration-none" href="{{ route('baak.jadwal.index') }}">&larr; Kembali ke jadwal</a>
            <h1 class="h3 mt-3 mb-1">{{ $jadwal ? 'Ubah Jadwal Kuliah' : 'Tambah Jadwal Kuliah' }}</h1>
            <p class="teks-bantu mb-0">Jadwal dengan riwayat pertemuan tidak dapat diubah agar rekam akademik tetap konsisten.</p>
        </section>

        @if ($errors->any())
            <div class="alert alert-danger" role="alert"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif

        <section class="kartu">
            <form method="POST" action="{{ $jadwal ? route('baak.jadwal.update', $jadwal) : route('baak.jadwal.store') }}" class="row g-3">
                @csrf
                @if ($jadwal)
                    @method('PUT')
                @endif
                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold" for="IDDOSEN">Dosen pengampu</label>
                    <select class="form-select" id="IDDOSEN" name="IDDOSEN" required>
                        <option value="">Pilih dosen</option>
                        @foreach ($dosen as $pengampu)
                            <option value="{{ $pengampu->IDDOSEN }}" @selected((string) old('IDDOSEN', $jadwal?->IDDOSEN) === (string) $pengampu->IDDOSEN)>{{ $pengampu->NAMADOSEN }} · {{ $pengampu->NIDN }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-3"><label class="form-label fw-semibold" for="KODEMATAKULIAH">Kode mata kuliah</label><input class="form-control" id="KODEMATAKULIAH" name="KODEMATAKULIAH" value="{{ old('KODEMATAKULIAH', $jadwal?->KODEMATAKULIAH) }}" maxlength="255" required></div>
                <div class="col-12 col-md-3"><label class="form-label fw-semibold" for="SKS">SKS</label><input class="form-control" type="number" id="SKS" name="SKS" value="{{ old('SKS', $jadwal?->SKS ?? 2) }}" min="1" max="30" required></div>
                <div class="col-12 col-md-8"><label class="form-label fw-semibold" for="NAMAMATAKULIAH">Nama mata kuliah</label><input class="form-control" id="NAMAMATAKULIAH" name="NAMAMATAKULIAH" value="{{ old('NAMAMATAKULIAH', $jadwal?->NAMAMATAKULIAH) }}" maxlength="255" required></div>
                <div class="col-12 col-md-4"><label class="form-label fw-semibold" for="KELAS">Kelas</label><input class="form-control" id="KELAS" name="KELAS" value="{{ old('KELAS', $jadwal?->KELAS) }}" maxlength="255" required></div>
                <div class="col-12 col-md-4">
                    <label class="form-label fw-semibold" for="HARI">Hari</label>
                    <select class="form-select" id="HARI" name="HARI" required>
                        @foreach (['SENIN', 'SELASA', 'RABU', 'KAMIS', 'JUMAT', 'SABTU'] as $hari)
                            <option value="{{ $hari }}" @selected(old('HARI', $jadwal?->HARI ?? 'SENIN') === $hari)>{{ ucfirst(strtolower($hari)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-4"><label class="form-label fw-semibold" for="JAMMULAI">Jam mulai</label><input class="form-control" type="time" id="JAMMULAI" name="JAMMULAI" value="{{ old('JAMMULAI', $jadwal ? substr($jadwal->JAMMULAI, 0, 5) : '08:00') }}" required></div>
                <div class="col-6 col-md-4"><label class="form-label fw-semibold" for="JAMSELESAI">Jam selesai</label><input class="form-control" type="time" id="JAMSELESAI" name="JAMSELESAI" value="{{ old('JAMSELESAI', $jadwal ? substr($jadwal->JAMSELESAI, 0, 5) : '10:00') }}" required></div>
                <div class="col-12 col-md-4"><label class="form-label fw-semibold" for="RUANGAN">Ruangan</label><input class="form-control" id="RUANGAN" name="RUANGAN" value="{{ old('RUANGAN', $jadwal?->RUANGAN) }}" maxlength="255" required></div>
                <div class="col-6 col-md-2"><label class="form-label fw-semibold" for="LINTANG">Lintang</label><input class="form-control" type="number" step="0.0000001" id="LINTANG" name="LINTANG" value="{{ old('LINTANG', $jadwal?->LINTANG ?? '-6.2000000') }}" required></div>
                <div class="col-6 col-md-2"><label class="form-label fw-semibold" for="BUJUR">Bujur</label><input class="form-control" type="number" step="0.0000001" id="BUJUR" name="BUJUR" value="{{ old('BUJUR', $jadwal?->BUJUR ?? '106.8166667') }}" required></div>
                <div class="col-6 col-md-2"><label class="form-label fw-semibold" for="RADIUSMETER">Radius (m)</label><input class="form-control" type="number" id="RADIUSMETER" name="RADIUSMETER" value="{{ old('RADIUSMETER', $jadwal?->RADIUSMETER ?? 100) }}" min="1" max="10000" required></div>
                <div class="col-6 col-md-2"><label class="form-label fw-semibold" for="MENITPENGINGAT">Pengingat (menit)</label><input class="form-control" type="number" id="MENITPENGINGAT" name="MENITPENGINGAT" value="{{ old('MENITPENGINGAT', $jadwal?->MENITPENGINGAT ?? 30) }}" min="0" max="1440" required></div>
                <div class="col-12 col-md-4">
                    <label class="form-label fw-semibold" for="MODEKEHADIRAN">Mode kehadiran</label>
                    <select class="form-select" id="MODEKEHADIRAN" name="MODEKEHADIRAN" required>
                        <option value="WAJIBKELAS" @selected(old('MODEKEHADIRAN', $jadwal?->MODEKEHADIRAN ?? 'WAJIBKELAS') === 'WAJIBKELAS')>Wajib di area kelas</option>
                        <option value="BEBAS" @selected(old('MODEKEHADIRAN', $jadwal?->MODEKEHADIRAN) === 'BEBAS')>Bebas lokasi</option>
                    </select>
                </div>
                <div class="col-12 d-flex gap-2">
                    <button class="btn btn-primary" type="submit">{{ $jadwal ? 'Simpan perubahan' : 'Buat jadwal' }}</button>
                    <a class="btn btn-light border" href="{{ route('baak.jadwal.index') }}">Batal</a>
                </div>
            </form>
        </section>
    </div>
@endsection
