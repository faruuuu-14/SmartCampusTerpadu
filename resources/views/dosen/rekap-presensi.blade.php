<!-- ========================================== -->
<!-- PEMBAHASAN / BAGIAN: Galeri dan Rekap Presensi Dosen -->
<!-- ========================================== -->
@extends('layout.app')

@section('judul', 'Rekap Presensi')

@section('konten')
    <!-- ========================================== -->
    <!-- PEMBAHASAN / BAGIAN: Daftar Rekap per Pertemuan -->
    <!-- ========================================== -->
    <section class="kartu mb-4">
        <h1 class="h3 mb-1">Rekap Presensi</h1>
        <p class="text-secondary mb-0">Foto hanya dapat dilihat dosen pengampu. Putuskan catatan yang memerlukan peninjauan.</p>
    </section>
    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    @foreach ($jadwal as $mataKuliah)
        <section class="kartu mb-4">
            <h2 class="h5">{{ $mataKuliah->NAMAMATAKULIAH }} ({{ $mataKuliah->KELAS }})</h2>
            @forelse ($mataKuliah->pertemuan as $pertemuan)
                <h3 class="h6 border-top pt-3">Pertemuan {{ $pertemuan->PERTEMUANKE }} &middot; {{ $pertemuan->TANGGALPERTEMUAN?->format('d/m/Y') }}</h3>
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead><tr><th>Mahasiswa</th><th>Foto</th><th>Wajah / risiko</th><th>GPS / jarak</th><th>Status</th><th>Keputusan</th></tr></thead>
                        <tbody>
                            @forelse ($pertemuan->presensi as $presensi)
                                <tr>
                                    <td>{{ $presensi->mahasiswa?->pengguna?->NAMALENGKAP }}<br><small>{{ $presensi->mahasiswa?->NIM }}</small></td>
                                    <td>
                                        @if ($presensi->FILEFOTO)
                                            <a href="{{ route('dosen.rekap.foto', $presensi) }}" target="_blank" rel="noopener noreferrer">
                                                <img src="{{ route('dosen.rekap.foto', $presensi) }}" alt="Foto presensi {{ $presensi->mahasiswa?->NIM }}" width="72" height="72" class="rounded object-fit-cover">
                                            </a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ $presensi->SKORWAJAH ?? '-' }} / {{ $presensi->SKORRISIKO }}</td>
                                    <td>{{ $presensi->LINTANG ?? '-' }}, {{ $presensi->BUJUR ?? '-' }}<br>{{ $presensi->JARAKMETER ?? '-' }} m (akurasi {{ $presensi->AKURASIGPS ?? '-' }} m)</td>
                                    <td>{{ $presensi->STATUSPRESENSI }}</td>
                                    <td>
                                        @if ($presensi->STATUSPRESENSI === 'PERLUREVIEW')
                                            <div class="d-flex gap-1">
                                                <form method="POST" action="{{ route('dosen.presensi.tinjau', $presensi) }}">
                                                    @csrf
                                                    <input type="hidden" name="KEPUTUSAN" value="HADIR">
                                                    <button class="btn btn-sm btn-success" type="submit">Setujui</button>
                                                </form>
                                                <form method="POST" action="{{ route('dosen.presensi.tinjau', $presensi) }}">
                                                    @csrf
                                                    <input type="hidden" name="KEPUTUSAN" value="DITOLAK">
                                                    <button class="btn btn-sm btn-outline-danger" type="submit">Tolak</button>
                                                </form>
                                            </div>
                                        @else
                                            {{ $presensi->peninjau?->NAMALENGKAP ?? '-' }}
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-secondary">Belum ada presensi.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @empty
                <p class="text-secondary">Belum ada pertemuan.</p>
            @endforelse
        </section>
    @endforeach
@endsection
