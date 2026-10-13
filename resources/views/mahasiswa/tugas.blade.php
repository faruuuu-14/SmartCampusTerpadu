<!-- ========================================== -->
<!-- PEMBAHASAN / BAGIAN: Daftar Tugas dan Pengumpulan Mahasiswa -->
<!-- ========================================== -->
@extends('layout.app')

@section('judul', 'Kelas dan Tugas')

@section('konten')
    <section class="kartu mb-4">
        <h1 class="h3 mb-1">Kelas &amp; Tugas</h1>
        <p class="teks-bantu mb-0">Tugas ditampilkan dari kelas yang tercatat pada KRS Anda.</p>
    </section>

    <div class="d-flex flex-column gap-3">
        @forelse ($tugas as $item)
            @php($pengumpulan = $item->pengumpulan->first())
            <article class="kartu">
                <div class="d-flex flex-wrap justify-content-between gap-3">
                    <div>
                        <span class="small teks-bantu">{{ $item->jadwal->KODEMATAKULIAH }} · {{ $item->jadwal->NAMAMATAKULIAH }}</span>
                        <h2 class="h5 mt-1 mb-1">{{ $item->JUDULTUGAS }}</h2>
                        <p class="small teks-bantu mb-2">Dosen: {{ $item->dosen->NAMADOSEN }} · Batas: {{ $item->TANGGALTENGGAT->format('d/m/Y H:i') }}</p>
                        @if ($item->DESKRIPSI)
                            <p class="mb-0">{{ $item->DESKRIPSI }}</p>
                        @endif
                    </div>
                    <span class="badge rounded-pill {{ $pengumpulan ? 'text-bg-success' : 'text-bg-secondary' }} align-self-start">
                        {{ $pengumpulan?->STATUSPENGUMPULAN ?? 'BELUM DIKUMPULKAN' }}
                    </span>
                </div>

                @if ($pengumpulan?->UMPANBALIK || $pengumpulan?->NILAI !== null)
                    <div class="alert alert-light border mt-3 mb-0">
                        Nilai: <strong>{{ $pengumpulan->NILAI ?? 'Belum dinilai' }}</strong>
                        @if ($pengumpulan->UMPANBALIK)
                            <span class="d-block mt-1">{{ $pengumpulan->UMPANBALIK }}</span>
                        @endif
                    </div>
                @endif

                @if ($pengumpulan?->STATUSPENGUMPULAN !== 'DINILAI')
                    <div class="border-top mt-3 pt-3">
                        <form class="d-flex flex-wrap align-items-end gap-2" method="POST" action="{{ route('mahasiswa.tugas.simpan', $item) }}" enctype="multipart/form-data">
                            @csrf
                            <div class="flex-grow-1">
                                <label class="form-label fw-semibold" for="berkas-{{ $item->IDTUGAS }}">{{ $pengumpulan ? 'Ganti berkas pengumpulan' : 'Unggah berkas pengumpulan' }}</label>
                                <input class="form-control" type="file" id="berkas-{{ $item->IDTUGAS }}" name="BERKAS" accept=".pdf,.doc,.docx" required>
                                <div class="form-text">PDF atau Word, maksimal 10 MB.</div>
                            </div>
                            <button class="btn btn-primary" type="submit">Simpan pengumpulan</button>
                        </form>
                        @if ($pengumpulan)
                            <form class="mt-2" method="POST" action="{{ route('mahasiswa.tugas.hapus', $item) }}" onsubmit="return confirm('Hapus pengumpulan tugas ini?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" type="submit">Hapus pengumpulan</button>
                            </form>
                        @endif
                    </div>
                @endif
            </article>
        @empty
            <div class="kartu daftar-tanpa-data">Belum ada tugas untuk mata kuliah pada KRS Anda.</div>
        @endforelse
    </div>

    <div class="mt-3">{{ $tugas->links() }}</div>
@endsection
