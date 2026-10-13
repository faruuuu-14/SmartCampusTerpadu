<!-- ========================================== -->
<!-- PEMBAHASAN / BAGIAN: Daftar dan CRUD Jadwal Kuliah -->
<!-- ========================================== -->
@extends('layout.app')

@section('judul', 'Jadwal Kuliah')

@section('konten')
    <section class="kartu mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h1 class="h3 mb-1">Jadwal Kuliah</h1>
                <p class="teks-bantu mb-0">Kelola pengampu, ruang, waktu kuliah, dan aturan geofence.</p>
            </div>
            <a class="btn btn-primary" href="{{ route('baak.jadwal.create') }}">Tambah jadwal</a>
        </div>
    </section>

    <section class="kartu">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead><tr><th>Mata kuliah</th><th>Pengampu</th><th>Jadwal</th><th>Lokasi</th><th>Aksi</th></tr></thead>
                <tbody>
                    @forelse ($jadwal as $item)
                        <tr>
                            <td><strong>{{ $item->KODEMATAKULIAH }} · {{ $item->NAMAMATAKULIAH }}</strong><div class="small teks-bantu">{{ $item->SKS }} SKS · {{ $item->KELAS }}</div></td>
                            <td>{{ $item->dosen?->NAMADOSEN ?? '—' }}</td>
                            <td>{{ $item->HARI }}, {{ substr($item->JAMMULAI, 0, 5) }}–{{ substr($item->JAMSELESAI, 0, 5) }}<div class="small teks-bantu">{{ $item->RUANGAN }}</div></td>
                            <td>{{ $item->RADIUSMETER }} m · {{ $item->MODEKEHADIRAN }}</td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('baak.jadwal.edit', $item) }}">Ubah</a>
                                    <form method="POST" action="{{ route('baak.jadwal.destroy', $item) }}" onsubmit="return confirm('Hapus jadwal ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" type="submit">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td class="daftar-tanpa-data" colspan="5">Belum ada jadwal kuliah.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $jadwal->links() }}
    </section>
@endsection
